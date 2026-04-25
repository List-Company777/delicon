<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Prefecture;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportUpstageXml extends Command
{
    protected $signature = 'import:upstage-xml
                            {--dry-run : 実際にはDBへの書き込みを行わない}
                            {--url= : フィードURLを直接指定（省略時はconfig値）}';

    protected $description = 'www.up-stage.info の XML フィードを取得してナイトワーク系求人を jobs/shops に同期する';

    /** インポート対象カテゴリ（XMLの <category> フィールド値） */
    private const ALLOWED_CATEGORIES = [
        'キャバクラ',
        'ガールズバー',
        'ホスト',
        'ホストクラブ',
        'ボーイズバー',
        'スナック',
        'ラウンジ',
        'コンカフェ',
        'クラブ',
        'バー',
        'パブ',
        'ラウンジバー',
    ];

    /** カテゴリ名 → genres.id */
    private const CATEGORY_GENRE_MAP = [
        'キャバクラ'   => 1,
        'ホスト'       => 2,
        'ホストクラブ' => 2,
        'ボーイズバー' => 3,
        'ガールズバー' => 4,
        'スナック'     => 5,
        'ラウンジ'     => 6,
        'コンカフェ'   => 7,
        'クラブ'       => 8,
        'バー'         => 9,
        'パブ'         => 11,
        'ラウンジバー' => 6,
    ];

    private bool $dryRun = false;
    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;
    private int $shopsCreated = 0;
    private int $plansActivated = 0;

    /** 今回のインポートで既にプラン同期済みの shop xml_id */
    private array $syncedShopXmlIds = [];

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('【DRY-RUN モード】DBへの書き込みは行いません');
        }

        $url = $this->option('url') ?: config('services.upstage.xml_feed_url');
        if (!$url) {
            $this->error('フィードURLが設定されていません。UPSTAGE_XML_FEED_URL を .env に設定してください');
            return self::FAILURE;
        }

        $this->info("フィード取得中: {$url}");

        try {
            $response = Http::timeout(30)->get($url);
        } catch (\Exception $e) {
            $this->error('フィード取得エラー: ' . $e->getMessage());
            Log::error('import:upstage-xml フィード取得失敗', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }

        if (!$response->ok()) {
            $this->error("HTTP {$response->status()} — フィード取得失敗");
            return self::FAILURE;
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOERROR);
        if ($xml === false) {
            $errors = libxml_get_errors();
            $this->error('XMLパース失敗: ' . ($errors[0]->message ?? '不明なエラー'));
            return self::FAILURE;
        }

        // マスタデータをまとめてキャッシュ
        $prefectures = Prefecture::all()->keyBy('name');
        $areas       = Area::all()->keyBy('name');
        $boyJobType  = JobType::where('slug', 'boy')->first();

        if (!$boyJobType) {
            $this->error('job_types テーブルに slug=boy のレコードが見つかりません');
            return self::FAILURE;
        }

        $importedXmlIds = [];

        foreach ($xml->job as $job) {
            $xmlId = $this->processJob($job, $prefectures, $areas, $boyJobType);
            if ($xmlId !== null) {
                $importedXmlIds[] = $xmlId;
            }
        }

        // 今回フィードに含まれなかった既存求人を非公開にする
        if (!$this->dryRun && count($importedXmlIds) > 0) {
            $deactivated = Job::where('xml_source', 'upstage')
                ->whereNotIn('xml_id', $importedXmlIds)
                ->where('status', 'active')
                ->update(['status' => 'inactive']);

            if ($deactivated > 0) {
                $this->info("フィードから削除された求人を非公開: {$deactivated} 件");
            }
        }

        $this->info(
            "完了 — 新規: {$this->created} 件 / 更新: {$this->updated} 件 / スキップ: {$this->skipped} 件 / 店舗新規: {$this->shopsCreated} 件 / プラン有効化: {$this->plansActivated} 件"
        );

        Log::info('import:upstage-xml 完了', [
            'created'         => $this->created,
            'updated'         => $this->updated,
            'skipped'         => $this->skipped,
            'shops_created'   => $this->shopsCreated,
            'plans_activated' => $this->plansActivated,
        ]);

        return self::SUCCESS;
    }

    /** 1件のジョブを処理し、成功したら xml_id を返す */
    private function processJob(
        \SimpleXMLElement $job,
        $prefectures,
        $areas,
        JobType $boyJobType,
    ): ?string {
        $category = trim((string) $job->category);

        if (!in_array($category, self::ALLOWED_CATEGORIES, true)) {
            $this->skipped++;
            return null;
        }

        $xmlId    = trim((string) $job->referencenumber);
        $shopName = trim((string) ($job->store ?: $job->company));
        $stateName = trim((string) $job->state);
        $cityName  = trim((string) $job->city);
        $title     = trim((string) $job->title);
        $desc      = trim((string) $job->description);
        $salary    = trim((string) $job->salary);
        $applyUrl  = trim((string) $job->applyurl);
        $expStr    = trim((string) $job->expdate);
        $timeshift = trim((string) $job->timeshift);

        if (empty($xmlId)) {
            $this->skipped++;
            return null;
        }

        // 都道府県マッチング（「東京」→「東京都」など）
        $prefecture = $prefectures->get($stateName)
            ?? $prefectures->get($stateName . '都')
            ?? $prefectures->get($stateName . '府')
            ?? $prefectures->get($stateName . '県');

        $area = $areas->get($cityName);

        $genreId   = self::CATEGORY_GENRE_MAP[$category] ?? null;
        $expiresAt = $this->parseExpDate($expStr);
        $wageData  = $this->parseSalary($salary);

        // XMLプラン情報（www.up-stage.info側で設定、0=フリープラン）
        $xmlBidPrice      = max(0, (int) $job->nightwork_bid_price);
        $xmlMonthlyBudget = max(0, (int) $job->nightwork_monthly_budget);

        // 店舗の find-or-create
        // xml_id には名前+都道府県のハッシュを使い、同一店舗の重複生成を防ぐ
        $shopXmlId = 'shop_' . md5($shopName . '_' . ($prefecture?->id ?? '0'));
        $shop = $this->findOrCreateShop(
            shopXmlId: $shopXmlId,
            name: $shopName,
            prefectureId: $prefecture?->id,
            areaId: $area?->id,
            genreId: $genreId,
        );

        if (!$shop) {
            $this->skipped++;
            return null;
        }

        // プラン同期（同一店舗は1インポートにつき1回のみ）
        if (!in_array($shopXmlId, $this->syncedShopXmlIds, true)) {
            $this->syncShopPlan($shop, $xmlBidPrice, $xmlMonthlyBudget);
            $this->syncedShopXmlIds[] = $shopXmlId;
        }

        // 求人の upsert
        $existing = Job::where('xml_source', 'upstage')->where('xml_id', $xmlId)->first();

        $jobData = [
            'shop_id'         => $shop->id,
            'job_type_id'     => $boyJobType->id,
            'area_id'         => $area?->id ?? $shop->area_id,
            'prefecture_id'   => $prefecture?->id ?? $shop->prefecture_id,
            'title'           => $title ?: ($shopName . ' ボーイ求人'),
            'description'     => $desc,
            'working_hours'   => $timeshift ?: null,
            'wage_type'       => $wageData['type'],
            'hourly_wage_min' => $wageData['min'],
            'hourly_wage_max' => $wageData['max'],
            'employment_type' => 'PART_TIME',
            'search_group'    => 'male',
            'is_hotlink'      => true,
            'hotlink_url'     => $applyUrl ?: null,
            'xml_source'      => 'upstage',
            'xml_id'          => $xmlId,
            'xml_enabled'     => true,
            'status'          => 'active',
            'expires_at'      => $expiresAt,
        ];

        if ($existing) {
            if (!$this->dryRun) {
                $existing->update($jobData);
            }
            $this->updated++;
            $this->line("  更新: [{$xmlId}] {$title}");
        } else {
            if (!$this->dryRun) {
                $jobData['published_at'] = now();
                Job::create($jobData);
            }
            $this->created++;
            $this->line("  新規: [{$xmlId}] {$title}");
        }

        return $xmlId;
    }

    /**
     * XML から受け取ったプラン情報を shop に反映する。
     *
     * - 0→有料: bid_price を設定し、初月の monthly_budget を即時チャージ
     * - 有料→0: bid_price を最低値（10）に戻す。残高はそのまま使い切り
     * - 有料継続: bid_price のみ更新（月次チャージは別コマンドで実施）
     */
    private function syncShopPlan(Shop $shop, int $xmlBidPrice, int $xmlMonthlyBudget): void
    {
        if ($this->dryRun) {
            if ($xmlBidPrice > 0 && $shop->xml_bid_price === 0) {
                $this->line("  [DRY] プラン有効化予定: {$shop->name} bid={$xmlBidPrice} budget+={$xmlMonthlyBudget}");
                $this->plansActivated++;
            }
            return;
        }

        $wasActive = $shop->xml_bid_price > 0;
        $nowActive = $xmlBidPrice > 0;

        // プラン情報が変わっていなければスキップ
        if ($shop->xml_bid_price === $xmlBidPrice && $shop->xml_monthly_budget === $xmlMonthlyBudget) {
            return;
        }

        $updates = [
            'xml_bid_price'      => $xmlBidPrice,
            'xml_monthly_budget' => $xmlMonthlyBudget,
        ];

        if (!$wasActive && $nowActive) {
            // フリー → 有料: bid_price を設定し、初月予算を即時チャージ
            $updates['bid_price']             = $xmlBidPrice;
            $updates['budget_balance']        = $shop->budget_balance + $xmlMonthlyBudget;
            $updates['xml_plan_activated_at'] = now();
            $this->plansActivated++;
            $this->line("  プラン有効化: {$shop->name} bid={$xmlBidPrice} 初月予算+{$xmlMonthlyBudget}円");
            Log::info('upstage プラン有効化', ['shop_id' => $shop->id, 'bid_price' => $xmlBidPrice, 'budget_added' => $xmlMonthlyBudget]);
        } elseif ($wasActive && !$nowActive) {
            // 有料 → フリー: 入札単価を最低値に戻す（残高はそのまま）
            $updates['bid_price'] = 10;
            $this->line("  プラン解除: {$shop->name} → 無料プランへ");
            Log::info('upstage プラン解除', ['shop_id' => $shop->id]);
        } elseif ($wasActive && $nowActive && $shop->bid_price !== $xmlBidPrice) {
            // 有料継続・bid_price 変更
            $updates['bid_price'] = $xmlBidPrice;
            $this->line("  入札単価変更: {$shop->name} {$shop->bid_price}→{$xmlBidPrice}円");
        }

        $shop->update($updates);
    }

    private function findOrCreateShop(
        string $shopXmlId,
        string $name,
        ?int $prefectureId,
        ?int $areaId,
        ?int $genreId,
    ): ?Shop {
        if (empty($name)) {
            return null;
        }

        $shop = Shop::where('xml_source', 'upstage')->where('xml_id', $shopXmlId)->first();
        if ($shop) {
            return $shop;
        }

        if ($this->dryRun) {
            $this->shopsCreated++;
            // dry-run では実体なしで返す
            $dummy = new Shop();
            $dummy->id = 0;
            $dummy->area_id = $areaId;
            $dummy->prefecture_id = $prefectureId;
            return $dummy;
        }

        $shop = Shop::create([
            'name'          => $name,
            'genre_id'      => $genreId,
            'prefecture_id' => $prefectureId,
            'area_id'       => $areaId,
            'status'        => 'inactive', // オーナー登録後に公開
            'xml_source'    => 'upstage',
            'xml_id'        => $shopXmlId,
            'xml_enabled'   => true,
        ]);

        $this->shopsCreated++;
        $this->line("  店舗新規: {$name}");

        return $shop;
    }

    /**
     * 給与文字列を wage_type / min / max に分解する
     * 例: "時給1,200円〜1,500円" → ['type'=>'hourly','min'=>1200,'max'=>1500]
     *     "日給20,000円"          → ['type'=>'daily','min'=>20000,'max'=>null]
     */
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
        $numbers = array_map(fn($n) => (int) str_replace(',', '', $n), $matches[0]);

        // 3桁未満（年・時など表記の数字）は除外
        $numbers = array_values(array_filter($numbers, fn($n) => $n >= 100));

        if (count($numbers) >= 1) {
            $result['min'] = $numbers[0];
        }
        if (count($numbers) >= 2) {
            $result['max'] = $numbers[1];
        }

        return $result;
    }

    /**
     * expdate文字列をCarbonに変換する
     * Standbやフィードの形式: YYYYMMDD, YYYY-MM-DD, YYYY/MM/DD
     */
    private function parseExpDate(string $expDate): ?Carbon
    {
        if (empty($expDate)) {
            return null;
        }

        try {
            $clean = preg_replace('/[^0-9]/', '', $expDate);
            if (strlen($clean) === 8) {
                return Carbon::createFromFormat('Ymd', $clean)->endOfDay();
            }
        } catch (\Exception) {
            // 無効な日付は無視
        }

        return null;
    }
}
