<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\AreaAddressMapping;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Prefecture;
use App\Models\Shop;
use App\Models\ShopDetail;
use App\Models\User;
use App\Models\XmlFeed;
use App\Services\ImageService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportXmlFeed extends Command
{
    protected $signature = 'import:xml-feed
                            {slug? : 連携先スラッグ（省略時は全アクティブフィード）}
                            {--dry-run : DBへの書き込みを行わない}';

    protected $description = '登録済みの外部XMLフィードから求人・店舗データを同期する';

    private bool $dryRun     = false;
    private int  $created    = 0;
    private int  $updated    = 0;
    private int  $skipped    = 0;
    private int  $shopsNew   = 0;
    private int  $plansAct   = 0;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('【DRY-RUN モード】DBへの書き込みは行いません');
        }

        $slug = $this->argument('slug');

        $feeds = $slug
            ? XmlFeed::where('slug', $slug)->where('status', 'active')->get()
            : XmlFeed::where('status', 'active')->get();

        if ($feeds->isEmpty()) {
            $this->warn('アクティブな連携フィードが見つかりません');
            return self::SUCCESS;
        }

        foreach ($feeds as $feed) {
            $this->line('');
            $this->info("▶ [{$feed->slug}] {$feed->name} ({$feed->feedTypeLabel()})");
            $this->importFeed($feed);
        }

        return self::SUCCESS;
    }

    private function importFeed(XmlFeed $feed): void
    {
        // URLはDB優先、空なら env の UPSTAGE_XML_FEED_URL へのフォールバック（www.up-stage.info互換）
        $url = $feed->url ?: config('services.upstage.xml_feed_url');

        if (!$url) {
            $this->error("  フィードURLが未設定です（{$feed->slug}）");
            return;
        }

        try {
            $response = Http::timeout(30)->get($url);
        } catch (\Exception $e) {
            $this->error("  フィード取得エラー: " . $e->getMessage());
            Log::error("import:xml-feed [{$feed->slug}] 取得失敗", ['error' => $e->getMessage()]);
            return;
        }

        if (!$response->ok()) {
            $this->error("  HTTP {$response->status()} — 取得失敗");
            return;
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOERROR);
        if ($xml === false) {
            $errors = libxml_get_errors();
            $this->error('  XMLパース失敗: ' . ($errors[0]->message ?? '不明なエラー'));
            return;
        }

        // マスタデータキャッシュ
        $prefectures    = Prefecture::all()->keyBy('name');
        // 名前の長い順にソート（より具体的なエリアを優先マッチ）
        $areas          = Area::all()->sortByDesc(fn($a) => mb_strlen($a->name));
        // 住所→エリアの手動マッピング（解決済みのみ）
        $addressMappings = AreaAddressMapping::with('area')->whereNotNull('area_id')
            ->get()->sortByDesc(fn($m) => mb_strlen($m->keyword));

        // feed_type に応じた job_type を決定（タイトル推定の fallback 用）
        $jobType = match ($feed->feed_type) {
            'staff_jobs' => JobType::where('target_gender', 'male')->orderBy('sort_order')->first(),
            'cast_jobs'  => JobType::where('target_gender', 'female')->orderBy('sort_order')->first(),
            default      => null,
        };

        // タイトル推定用に全 JobType をキャッシュ
        $allJobTypes = JobType::all()->keyBy('slug');

        if ($feed->feed_type !== 'business_info' && !$jobType) {
            $this->error("  対応するJobTypeが見つかりません（{$feed->feed_type}）");
            return;
        }

        $importedXmlIds = [];
        $syncedShopIds  = [];
        $this->created = $this->updated = $this->skipped = $this->shopsNew = $this->plansAct = 0;

        if ($feed->feed_type === 'business_info') {
            // 店舗情報フィード：jobs は作らず shops / shop_details を upsert
            foreach ($xml->job as $entry) {
                $xmlId = $this->processBusinessEntry($entry, $feed, $prefectures, $areas, $addressMappings);
                if ($xmlId !== null) {
                    $importedXmlIds[] = $xmlId;
                }
            }

            if (!$this->dryRun && count($importedXmlIds) > 0) {
                $disappeared = Shop::where('xml_source', $feed->slug)
                    ->whereNotIn('xml_id', $importedXmlIds)
                    ->where('status', 'active')
                    ->get();

                $this->deactivateShops($disappeared, $feed);
            }
        } else {
            foreach ($xml->job as $job) {
                $xmlId = $this->processJob($job, $feed, $prefectures, $areas, $addressMappings, $jobType, $syncedShopIds, $allJobTypes);
                if ($xmlId !== null) {
                    $importedXmlIds[] = $xmlId;
                }
            }

            if (!$this->dryRun && count($importedXmlIds) > 0) {
                // フィードから消えた求人を非公開に
                $deactivated = Job::where('xml_source', $feed->slug)
                    ->whereNotIn('xml_id', $importedXmlIds)
                    ->where('status', 'active')
                    ->update(['status' => 'inactive']);

                if ($deactivated > 0) {
                    $this->line("  フィードから削除された求人を非公開: {$deactivated} 件");
                }

                // 全求人が消えた店舗のプランを無料に落とす
                $disappearedShops = Shop::where('xml_source', $feed->slug)
                    ->where('xml_bid_price', '>', 0)
                    ->whereDoesntHave('jobs', fn($q) => $q->where('xml_source', $feed->slug)->where('status', 'active'))
                    ->get();

                $this->deactivateShops($disappearedShops, $feed);
            }
        }

        if (!$this->dryRun) {
            $feed->update(['last_imported_at' => now()]);
        }

        $suffix = $feed->is_own_site && $this->plansAct > 0 ? " / プラン有効化: {$this->plansAct} 件" : '';
        $this->info(
            "  完了 — 新規: {$this->created} / 更新: {$this->updated} / スキップ: {$this->skipped} / 店舗新規: {$this->shopsNew}{$suffix}"
        );

        Log::info("import:xml-feed [{$feed->slug}] 完了", [
            'created'  => $this->created,
            'updated'  => $this->updated,
            'skipped'  => $this->skipped,
            'shops'    => $this->shopsNew,
            'plans'    => $this->plansAct,
        ]);
    }

    private function processJob(
        \SimpleXMLElement $job,
        XmlFeed $feed,
        $prefectures,
        $areas,
        $addressMappings,
        ?JobType $jobType,
        array &$syncedShopIds,
        $allJobTypes = null,
    ): ?string {
        $category = trim(explode('、', (string) $job->category)[0]);

        // カテゴリフィルタ（null=全件許可）
        $allowed = $feed->allowed_categories;
        if ($allowed !== null && !in_array($category, $allowed, true)) {
            $this->skipped++;
            return null;
        }

        $xmlId           = trim((string) $job->referencenumber);
        $shopName        = trim((string) ($job->store ?: $job->company));
        $stateName       = trim((string) $job->state);
        $cityName        = trim((string) $job->city);
        $notificationEmail = trim((string) $job->notification_email);
        $title     = trim((string) $job->title);
        $desc      = trim((string) $job->description);
        $salary    = trim((string) $job->salary);
        $applyUrl  = trim((string) ($job->applyurl ?: $job->url));
        $expStr    = trim((string) $job->expdate);
        $timeshift = trim((string) $job->timeshift);
        $imageUrl  = trim((string) ($job->image_url ?: $job->imageurls));

        if (empty($xmlId)) {
            $this->skipped++;
            return null;
        }

        $prefecture = $prefectures->get($stateName)
            ?? $prefectures->get($stateName . '都')
            ?? $prefectures->get($stateName . '府')
            ?? $prefectures->get($stateName . '県');

        // cityは住所フルテキストの場合があるため、最長一致でエリアを特定
        $area = $areas->first(fn($a) => mb_strpos($cityName, $a->name) !== false);

        // エリア未解決 → 手動マッピングテーブルを照合
        if (!$area && $cityName) {
            $matched = $addressMappings->first(fn($m) => mb_strpos($cityName, $m->keyword) !== false);
            if ($matched) {
                $area = $matched->area;
            } else {
                // 未解決として記録（同一キーワードが既にあればスキップ）
                AreaAddressMapping::firstOrCreate(
                    ['keyword' => $cityName],
                    ['example_address' => $cityName, 'area_id' => null]
                );
            }
        }
        $genreId = ($feed->category_genre_map ?? [])[$category] ?? null;
        $wageData  = $this->parseSalary($salary);
        $expiresAt = $this->parseExpDate($expStr);

        // 店舗の find-or-create
        $shop = $this->findOrCreateShop($shopName, $feed->slug, $prefecture?->id, $area?->id, $genreId);
        if (!$shop) {
            $this->skipped++;
            return null;
        }

        // 住所フィールドを補完（未設定の場合のみ）
        $xmlAddress = trim($stateName . $cityName);
        if ($xmlAddress && !$shop->address && !$this->dryRun) {
            $shop->update(['address' => $xmlAddress]);
        }

        // プラン同期（自社サイトかつ未同期の店舗のみ）。shop->id=0はdry-run用ダミー
        $shopKey = $shop->id ?: ('dry_' . $shopName . '_' . ($prefecture?->id ?? '0') . '_' . ($area?->id ?? '0'));
        if ($feed->is_own_site && !in_array($shopKey, $syncedShopIds, true)) {
            $this->syncShopPlan($shop, $job, $feed);

            if (empty($notificationEmail) || !filter_var($notificationEmail, FILTER_VALIDATE_EMAIL)) {
                $this->warn("  [WARN] notification_email が未設定: [{$xmlId}] {$shopName} — 応募通知が届きません");
                Log::warning("import:xml-feed [{$feed->slug}] notification_email 未設定", [
                    'xml_id' => $xmlId,
                    'shop'   => $shopName,
                ]);
            } else {
                $this->syncShopOwner($shop, $notificationEmail);
            }

            $syncedShopIds[] = $shopKey;
        }

        // 求人タイプの search_group
        $searchGroup = match ($feed->feed_type) {
            'staff_jobs' => 'male',
            'cast_jobs'  => 'female',
            default      => 'male',
        };

        // 他社サイトは常にhotlink（クリックは元サイトへ）
        $isHotlink = !$feed->is_own_site;

        // 職種解決: XMLフィールド → タイトル推定 → feed_typeデフォルト
        $xmlJobTypeStr   = trim((string) ($job->job_type ?? ''));
        $resolvedJobType = ($allJobTypes ? $this->inferJobTypeFromXmlField($xmlJobTypeStr, $allJobTypes, $category) : null)
            ?? ($allJobTypes ? $this->inferJobTypeFromTitle($title, $allJobTypes) : null)
            ?? $jobType;

        $existing = Job::where('xml_source', $feed->slug)->where('xml_id', $xmlId)->first();

        $jobData = [
            'shop_id'         => $shop->id,
            'job_type_id'     => $resolvedJobType->id,
            'area_id'         => $area?->id ?? $shop->area_id,
            'prefecture_id'   => $prefecture?->id ?? $shop->prefecture_id,
            'title'           => $title ?: ($shopName . ' 求人'),
            'description'     => $desc,
            'working_hours'   => $timeshift ?: null,
            'wage_type'       => $wageData['type'],
            'hourly_wage_min' => $wageData['min'],
            'hourly_wage_max' => $wageData['max'],
            'employment_type' => 'PART_TIME',
            'search_group'    => $searchGroup,
            'is_hotlink'      => $isHotlink,
            'hotlink_url'     => $applyUrl ?: null,
            'xml_source'      => $feed->slug,
            'xml_id'          => $xmlId,
            'xml_enabled'     => true,
            'status'          => 'active',
            'expires_at'      => $expiresAt,
            'xml_image_url'        => $imageUrl ?: null,
        ];

        if ($existing) {
            if (!$this->dryRun) {
                $existing->update($jobData);
                $this->syncJobImage($existing->fresh(), $imageUrl, $shop->id);
            }
            $this->updated++;
            $this->line("  更新: [{$xmlId}] {$title}");
        } else {
            if (!$this->dryRun) {
                $jobData['published_at'] = now();
                $newJob = Job::create($jobData);
                $this->syncJobImage($newJob, $imageUrl, $shop->id);
            }
            $this->created++;
            $this->line("  新規: [{$xmlId}] {$title}");
        }

        return $xmlId;
    }

    private function inferJobTypeFromXmlField(string $value, $allJobTypes, string $category = ''): ?JobType
    {
        if ($value === '') return null;

        // カテゴリ固有の上書き
        if ($category === '無料案内所' && $value === '店舗スタッフ') {
            return $allJobTypes->get('annai');
        }

        $map = [
            '黒服・ボーイ'       => 'kurofuku',
            '送迎ドライバー'      => 'driver',
            '配送ドライバー'      => 'driver',
            'ヘアメイク'         => 'hair-makeup',
            'キッチン'           => 'kitchen',
            'エスコート'         => 'escort',
            'キャッシャー'       => 'casher',
            'マネージャー'       => 'kanbu',
            'スカウト・外販スタッフ' => 'gaihan',
            'メンズキャスト'      => 'mens-cast',
            'バーテンダー'       => 'bartender',
            'ＷＥＢデザイナー'    => 'web-designer',
            '事務・経理'         => 'office',
            '清掃員'             => 'cleaning',
        ];

        return isset($map[$value]) ? $allJobTypes->get($map[$value]) : null;
    }

    private function inferJobTypeFromTitle(string $title, $allJobTypes): ?JobType
    {
        // 優先度順にキーワードマッチ
        $map = [
            'driver'      => ['ドライバー', '運転'],
            'kitchen'     => ['キッチン'],
            'kurofuku'    => ['黒服'],
            'escort'      => ['エスコート'],
            'gaihan'      => ['外販', 'スカウト'],
            'casher'      => ['キャッシャー'],
            'bartender'   => ['バーテンダー'],
            'kanbu'       => ['幹部'],
            'annai'       => ['案内'],
            'boy'         => ['ボーイ'],
            'hair-makeup' => ['ヘアメイク', 'ヘア'],
            'web-designer' => ['WEB', 'Web', 'ウェブ', 'デザイナー'],
            'office'      => ['経理', '事務'],
            'cleaning'    => ['清掃'],
        ];

        foreach ($map as $slug => $keywords) {
            foreach ($keywords as $kw) {
                if (mb_strpos($title, $kw) !== false) {
                    return $allJobTypes->get($slug);
                }
            }
        }

        return null;
    }

    /**
     * XMLから消えた店舗を処理する。
     * 自社媒体 or オーナーあり → bid_price=10（無料継続）
     * 他社 + オーナーなし → status=inactive（非公開）
     */
    private function deactivateShops($shops, XmlFeed $feed): void
    {
        $freed    = 0;
        $hidden   = 0;

        foreach ($shops as $shop) {
            $hasOwner = $shop->users()->wherePivot('role', 'owner')->exists();

            if ($feed->is_own_site || $hasOwner) {
                $shop->update([
                    'bid_price'       => 30,
                    'xml_bid_price'   => 0,
                    'xml_enabled'     => false,
                    'xml_disabled_at' => now(),
                ]);
                $freed++;
            } else {
                $shop->update([
                    'status'          => 'inactive',
                    'xml_bid_price'   => 0,
                    'xml_enabled'     => false,
                    'xml_disabled_at' => now(),
                ]);
                $hidden++;
            }
        }

        if ($freed > 0)  $this->line("  入稿消滅→無料継続: {$freed} 店舗");
        if ($hidden > 0) $this->line("  入稿消滅→非公開: {$hidden} 店舗");
    }

    /**
     * business_info フィード：1エントリ = 1店舗として shops / shop_details を upsert する
     */
    private function processBusinessEntry(
        \SimpleXMLElement $entry,
        XmlFeed $feed,
        $prefectures,
        $areas,
        $addressMappings,
    ): ?string {
        $category = trim(explode('、', (string) $entry->category)[0]);

        $allowed = $feed->allowed_categories;
        if ($allowed !== null && !in_array($category, $allowed, true)) {
            $this->skipped++;
            return null;
        }

        $xmlId             = trim((string) $entry->referencenumber);
        $shopName          = trim((string) ($entry->store ?: $entry->company));
        $stateName         = trim((string) $entry->state);
        $cityName          = trim((string) $entry->city);
        $notificationEmail = trim((string) $entry->notification_email);
        $description       = trim((string) $entry->description);
        $websiteUrl        = trim((string) ($entry->applyurl ?: $entry->url));
        $imageUrl          = trim((string) ($entry->image_url ?: $entry->imageurls));
        $tel               = trim((string) $entry->tel);

        if (empty($xmlId) || empty($shopName)) {
            $this->skipped++;
            return null;
        }

        $prefecture = $prefectures->get($stateName)
            ?? $prefectures->get($stateName . '都')
            ?? $prefectures->get($stateName . '府')
            ?? $prefectures->get($stateName . '県');

        $area = $areas->first(fn($a) => mb_strpos($cityName, $a->name) !== false);

        if (!$area && $cityName) {
            $matched = $addressMappings->first(fn($m) => mb_strpos($cityName, $m->keyword) !== false);
            if ($matched) {
                $area = $matched->area;
            } else {
                AreaAddressMapping::firstOrCreate(
                    ['keyword' => $cityName],
                    ['example_address' => $cityName, 'area_id' => null]
                );
            }
        }

        $genreId   = ($feed->category_genre_map ?? [])[$category] ?? null;
        $isHotlink = !$feed->is_own_site;

        if ($this->dryRun) {
            $existing = Shop::where('xml_source', $feed->slug)->where('xml_id', $xmlId)->first();
            $label = $existing ? '更新予定' : '新規予定';
            $this->line("  [DRY] {$label}: [{$xmlId}] {$shopName}");
            if (!$existing) $this->shopsNew++;
            else $this->updated++;
            return $xmlId;
        }

        $existing = Shop::where('xml_source', $feed->slug)->where('xml_id', $xmlId)->first();

        $xmlAddress = trim($stateName . $cityName);
        $shopData = [
            'name'          => $shopName,
            'genre_id'      => $genreId ?? $existing?->genre_id,
            'prefecture_id' => $prefecture?->id ?? $existing?->prefecture_id,
            'area_id'       => $area?->id ?? $existing?->area_id,
            'tel'           => $tel ?: ($existing?->tel),
            'address'       => $xmlAddress ?: ($existing?->address),
            'status'        => 'active',
            'xml_source'    => $feed->slug,
            'xml_id'        => $xmlId,
            'xml_enabled'   => true,
        ];

        if ($existing) {
            $existing->update($shopData);
            $shop = $existing->fresh();
            $this->updated++;
            $this->line("  更新: [{$xmlId}] {$shopName}");
        } else {
            $shop = Shop::create($shopData);
            $this->shopsNew++;
            $this->line("  店舗新規: [{$xmlId}] {$shopName}");
        }

        // shop_details を upsert
        ShopDetail::updateOrCreate(
            ['shop_id' => $shop->id],
            array_filter([
                'is_hotlink'  => $isHotlink,
                'hotlink_url' => $isHotlink ? ($websiteUrl ?: null) : null,
                'content'     => $description ?: null,
            ], fn($v) => $v !== null)
        );

        // メイン画像を同期（URLが変わったときのみ再ダウンロード）
        $this->syncShopMainImage($shop, $imageUrl);

        // プラン同期・オーナー同期（自社サイトのみ）
        if ($feed->is_own_site) {
            $this->syncShopPlan($shop, $entry, $feed);

            if (empty($notificationEmail) || !filter_var($notificationEmail, FILTER_VALIDATE_EMAIL)) {
                $this->warn("  [WARN] notification_email が未設定: [{$xmlId}] {$shopName} — 応募通知が届きません");
                Log::warning("import:xml-feed [{$feed->slug}] notification_email 未設定", [
                    'xml_id' => $xmlId,
                    'shop'   => $shopName,
                ]);
            } else {
                $this->syncShopOwner($shop, $notificationEmail);
            }
        }

        return $xmlId;
    }

    /**
     * XML画像URLが変わったときだけダウンロードして shops.main_image を更新する
     */
    private function syncShopMainImage(Shop $shop, string $imageUrl): void
    {
        if (empty($imageUrl)) {
            return;
        }

        if ($shop->xml_image_url === $imageUrl && $shop->main_image) {
            return;
        }

        $imagePath = (new ImageService)->saveShopMainImageFromUrl($imageUrl, $shop->id);

        if ($imagePath) {
            $shop->update(['main_image' => $imagePath, 'xml_image_url' => $imageUrl]);
            $this->line("  店舗画像保存: [{$shop->xml_id}] {$imageUrl}");
        }
    }

    /**
     * XML画像URLが変わったときだけダウンロードして image_path を更新する
     */
    private function syncJobImage(Job $job, string $imageUrl, int $shopId): void
    {
        if (empty($imageUrl)) {
            return;
        }

        // 前回取り込み済みのURLと同じなら再ダウンロードしない
        if ($job->xml_image_url === $imageUrl && $job->image_path) {
            return;
        }

        $imagePath = (new ImageService)->saveJobImageFromUrl($imageUrl, $shopId, $job->id);

        if ($imagePath) {
            $job->update(['image_path' => $imagePath]);
            $this->line("  画像保存: [{$job->xml_id}] {$imageUrl}");
        }
    }

    private function findOrCreateShop(
        string $name,
        string $feedSlug,
        ?int $prefectureId,
        ?int $areaId,
        ?int $genreId,
    ): ?Shop {
        if (empty($name)) {
            return null;
        }

        // 新キー：店名 + 都道府県 + エリア（支店を区別するためエリアを含む）
        $newKey    = 'shop_' . md5($name . '_' . ($prefectureId ?? '0') . '_' . ($areaId ?? '0'));
        // 旧キー：店名 + 都道府県のみ（既存レコードとの互換用）
        $legacyKey = 'shop_' . md5($name . '_' . ($prefectureId ?? '0'));

        // 新キーで検索
        $shop = Shop::where('xml_source', $feedSlug)->where('xml_id', $newKey)->first();
        if ($shop) {
            // prefecture_id / area_id が後から判明した場合は更新
            $updates = [];
            if ($prefectureId && !$shop->prefecture_id) $updates['prefecture_id'] = $prefectureId;
            if ($areaId      && !$shop->area_id)       $updates['area_id']       = $areaId;
            if ($updates && !$this->dryRun) $shop->update($updates);
            return $shop;
        }

        // 旧キーで検索（既存レコードの自動移行）
        $legacy = Shop::where('xml_source', $feedSlug)->where('xml_id', $legacyKey)->first();
        if ($legacy) {
            if ($legacy->area_id === $areaId || $areaId === null) {
                // 同一エリア → 同一店舗とみなし旧キーを新キーへ移行
                if (!$this->dryRun) {
                    $updates = ['xml_id' => $newKey];
                    if ($prefectureId && !$legacy->prefecture_id) $updates['prefecture_id'] = $prefectureId;
                    if ($areaId      && !$legacy->area_id)       $updates['area_id']       = $areaId;
                    $legacy->update($updates);
                }
                return $legacy;
            }
            // エリアが違う → 別支店として新規作成へ
            $this->line("  別支店検出: {$name}（既存エリア: {$legacy->area_id} / 新エリア: {$areaId}）");
        }

        // prefecture=null で作成された旧キーを検索（後から都道府県が追加されたケース）
        $nullPrefKey = 'shop_' . md5($name . '_0_' . ($areaId ?? '0'));
        if ($prefectureId && $nullPrefKey !== $newKey) {
            $nullPrefShop = Shop::where('xml_source', $feedSlug)->where('xml_id', $nullPrefKey)->first();
            if ($nullPrefShop) {
                if (!$this->dryRun) {
                    $nullPrefShop->update(['xml_id' => $newKey, 'prefecture_id' => $prefectureId]);
                    $this->line("  都道府県更新: {$name} → prefecture_id={$prefectureId}");
                }
                return $nullPrefShop;
            }
        }

        if ($this->dryRun) {
            $this->shopsNew++;
            $dummy = new Shop();
            $dummy->id = 0;
            $dummy->area_id = $areaId;
            $dummy->prefecture_id = $prefectureId;
            $dummy->xml_bid_price = 0;
            $dummy->xml_monthly_budget = 0;
            return $dummy;
        }

        $shop = Shop::create([
            'name'          => $name,
            'genre_id'      => $genreId,
            'prefecture_id' => $prefectureId,
            'area_id'       => $areaId,
            'status'        => 'active',
            'xml_source'    => $feedSlug,
            'xml_id'        => $newKey,
            'xml_enabled'   => true,
        ]);

        $this->shopsNew++;
        $this->line("  店舗新規: {$name}");

        return $shop;
    }

    /**
     * 自社サイトのみ: XML の入札単価・月次予算フィールドを読み取ってショップに反映する
     */
    private function syncShopPlan(Shop $shop, \SimpleXMLElement $job, XmlFeed $feed): void
    {
        if (!$feed->bid_price_xml_field) {
            return;
        }

        $xmlBidPrice      = max(0, (int) ($job->{$feed->bid_price_xml_field} ?? 0));
        $xmlMonthlyBudget = $feed->monthly_budget_xml_field
            ? max(0, (int) ($job->{$feed->monthly_budget_xml_field} ?? 0))
            : 0;

        if ($this->dryRun) {
            if ($xmlBidPrice > 0 && $shop->xml_bid_price === 0) {
                $this->line("  [DRY] プラン有効化予定: {$shop->name} bid={$xmlBidPrice}");
                $this->plansAct++;
            }
            return;
        }

        $wasActive = $shop->xml_bid_price > 0;
        $nowActive = $xmlBidPrice > 0;

        if ($shop->xml_bid_price === $xmlBidPrice && $shop->xml_monthly_budget === $xmlMonthlyBudget) {
            // 自社サイト: hasBudget()=false になっていたら補充
            if ($feed->is_own_site && $wasActive && !$this->dryRun && !$shop->hasBudget()) {
                $shop->update(['budget_balance' => 999999]);
                $this->line("  予算補充: {$shop->name} → 999,999円");
            }
            return;
        }

        $updates = [
            'xml_bid_price'      => $xmlBidPrice,
            'xml_monthly_budget' => $xmlMonthlyBudget,
        ];

        if (!$wasActive && $nowActive) {
            $updates['bid_price']             = $xmlBidPrice;
            // 自社サイトはbudget_balanceで表示制限しない（実質無制限）
            $updates['budget_balance']        = $feed->is_own_site ? 999999 : $shop->budget_balance + $xmlMonthlyBudget;
            $updates['xml_plan_activated_at'] = now();
            $this->plansAct++;
            $this->line("  プラン有効化: {$shop->name} bid={$xmlBidPrice} +{$xmlMonthlyBudget}円");
        } elseif ($wasActive && !$nowActive) {
            $updates['bid_price'] = 30;
            $this->line("  プラン解除: {$shop->name}");
        } elseif ($wasActive && $nowActive && $shop->bid_price !== $xmlBidPrice) {
            $updates['bid_price'] = $xmlBidPrice;
            $this->line("  入札単価変更: {$shop->name} {$shop->bid_price}→{$xmlBidPrice}円");
        }

        $shop->update($updates);
    }

    private function syncShopOwner(Shop $shop, string $email): void
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $currentOwner = $shop->users()->wherePivot('role', 'owner')->first();

        // 既にオーナーがいてメールアドレスも同じならスキップ
        if ($currentOwner && $currentOwner->email === $email) {
            return;
        }

        if ($this->dryRun) {
            if ($currentOwner) {
                $this->line("  [DRY] オーナー付け替え予定: {$shop->name} {$currentOwner->email} → {$email}");
            } else {
                $this->line("  [DRY] オーナー自動作成予定: {$email} → {$shop->name}");
            }
            return;
        }

        $newOwner = User::firstOrCreate(
            ['email' => $email],
            [
                'name'              => $shop->name . ' 担当者',
                'password'          => Str::random(24),
                'email_verified_at' => now(),
            ]
        );

        if ($currentOwner) {
            $shop->users()->detach($currentOwner->id);
            $shop->users()->attach($newOwner->id, ['role' => 'owner']);
            $this->line("  オーナー付け替え: {$shop->name} {$currentOwner->email} → {$email}（" . ($newOwner->wasRecentlyCreated ? '新規作成' : '既存ユーザー') . '）');
        } else {
            $shop->users()->attach($newOwner->id, ['role' => 'owner']);
            $this->line("  オーナー紐付け: {$shop->name} → {$email}（" . ($newOwner->wasRecentlyCreated ? '新規作成' : '既存ユーザー') . '）');
        }
    }

    private function parseSalary(string $salary): array
    {
        $result = ['type' => 'hourly', 'min' => null, 'max' => null];
        if (empty($salary)) {
            return $result;
        }

        if (str_contains($salary, '月給') || str_contains($salary, '月収')) {
            $result['type'] = 'monthly';
        } elseif (str_contains($salary, '日給') || str_contains($salary, '日払')) {
            $result['type'] = 'daily';
        }

        preg_match_all('/[\d,]+/', $salary, $matches);
        $numbers = array_values(array_filter(
            array_map(fn($n) => (int) str_replace(',', '', $n), $matches[0]),
            fn($n) => $n >= 100
        ));

        if (count($numbers) >= 1) $result['min'] = $numbers[0];
        if (count($numbers) >= 2) $result['max'] = $numbers[1];

        return $result;
    }

    private function parseExpDate(string $expDate): ?Carbon
    {
        if (empty($expDate)) {
            return null;
        }
        try {
            // 「2026-04-30 23:59:59」形式
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $expDate)) {
                return Carbon::parse($expDate);
            }
            // 「20260430」形式
            $clean = preg_replace('/[^0-9]/', '', $expDate);
            if (strlen($clean) === 8) {
                return Carbon::createFromFormat('Ymd', $clean)->endOfDay();
            }
        } catch (\Exception) { // 日付パース失敗は無視（不正な文字列のため）
        }
        return null;
    }
}
