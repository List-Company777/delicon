<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\JobAlert;
use App\Models\JobAlertSession;
use App\Models\JobType;
use App\Models\LineMessageLog;
use App\Models\Shop;
use App\Models\WebAlertToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $secret = config('services.line.messaging_channel_secret');
        if ($secret) {
            $signature = $request->header('X-Line-Signature') ?? '';
            $expected  = base64_encode(hash_hmac('sha256', $request->getContent(), $secret, true));
            if (!hash_equals($expected, $signature)) {
                Log::warning('LINE webhook: 署名検証失敗');
                return response('Unauthorized', 401);
            }
        }

        foreach ($request->json('events', []) as $event) {
            $this->handleEvent($event);
        }

        return response('OK');
    }

    private function handleEvent(array $event): void
    {
        if ($event['type'] === 'follow') {
            $this->handleFollow($event);
            return;
        }

        if ($event['type'] === 'unfollow') {
            $userId = $event['source']['userId'] ?? null;
            if ($userId) {
                JobAlert::where('line_user_id', $userId)->update(['is_active' => false]);
                JobAlertSession::where('line_user_id', $userId)->delete();
                Log::info("LINE unfollow: job_alerts deactivated for {$userId}");
            }
            return;
        }

        if ($event['type'] !== 'message') return;
        if (($event['message']['type'] ?? '') !== 'text') return;

        $userId     = $event['source']['userId'] ?? null;
        $text       = trim($event['message']['text']);
        $replyToken = $event['replyToken'] ?? null;

        if (!$userId || !$replyToken) return;

        // 進行中の求人アラート登録セッションがあれば優先
        $session = JobAlertSession::where('line_user_id', $userId)->first();
        if ($session && !$session->isExpired()) {
            $this->handleAlertStep($session, $text, $replyToken, $userId);
            return;
        }
        if ($session && $session->isExpired()) {
            $session->delete();
        }

        // キーワードで分岐
        if (in_array($text, ['登録', 'アラート', '求人アラート', '求人'], true)) {
            $this->startAlertRegistration($userId, $replyToken);
            return;
        }

        if (in_array($text, ['解除', '停止', 'アラート解除', 'アラート停止'], true)) {
            $this->deactivateAlert($userId, $replyToken);
            return;
        }

        // Webフォームからのアラート登録トークン
        if (preg_match('/^ALERT-([A-Za-z0-9]{32})$/', $text, $matches)) {
            Log::info("LINE ALERT token received from {$userId}: " . $matches[1]);
            $this->handleWebAlertToken($matches[1], $userId, $replyToken);
            return;
        }

        // 店舗オーナー用: NW-XXX コード
        if (preg_match('/^NW-(\d+)$/i', $text, $matches)) {
            $this->handleShopCode((int) $matches[1], $userId, $replyToken);
            return;
        }

        // その他のメッセージ
        $this->reply($replyToken,
            "求人アラートの登録はサイトの求人一覧ページから行えます。\n\n"
            . "アラートを解除する場合は「解除」と送信してください。"
        );
    }

    // --------------------------------------------------------
    // フォロー時のウェルカムメッセージ
    // --------------------------------------------------------
    private function handleFollow(array $event): void
    {
        $replyToken = $event['replyToken'] ?? null;
        if (!$replyToken) return;

        $this->reply($replyToken,
            "ナイトワークリスト 通知BOTです！\n\n"
            . "条件に合う新着求人をLINEでお知らせします。\n\n"
            . "▼ 求人アラートの登録\n"
            . "サイトの求人一覧ページ下部の「新着求人をLINEで受け取る」から登録できます。\n\n"
            . "▼ 応募通知設定（店舗オーナーの方）\n"
            . "管理画面に表示されている登録コード（例：NW-123）を送信してください。"
        );
    }

    // --------------------------------------------------------
    // 求人アラート登録フロー
    // --------------------------------------------------------
    private function startAlertRegistration(string $userId, string $replyToken): void
    {
        JobAlertSession::updateOrCreate(
            ['line_user_id' => $userId],
            ['step' => 'gender', 'gender' => null, 'area_id' => null, 'expires_at' => now()->addMinutes(30)]
        );

        $this->replyWithQuickReplies($replyToken,
            "どちらの求人を希望しますか？",
            [
                ['label' => '👩 女性ナイトワーク', 'text' => '女性'],
                ['label' => '👨 男性ナイトワーク', 'text' => '男性'],
                ['label' => '🔄 両方', 'text' => '両方'],
            ]
        );
    }

    private function handleAlertStep(JobAlertSession $session, string $text, string $replyToken, string $userId): void
    {
        switch ($session->step) {
            case 'gender':
                $gender = match($text) {
                    '女性', '女性ナイトワーク', 'female' => 'female',
                    '男性', '男性ナイトワーク', 'male'   => 'male',
                    '両方', 'both'                       => 'both',
                    default => null,
                };
                if (!$gender) {
                    $this->replyWithQuickReplies($replyToken, "「女性」「男性」または「両方」を選択してください。", [
                        ['label' => '👩 女性ナイトワーク', 'text' => '女性'],
                        ['label' => '👨 男性ナイトワーク', 'text' => '男性'],
                        ['label' => '🔄 両方', 'text' => '両方'],
                    ]);
                    return;
                }
                $session->update(['step' => 'area', 'gender' => $gender]);
                $this->sendAreaQuickReply($replyToken);
                break;

            case 'area':
                if ($text === '全国') {
                    $session->update(['step' => 'job_type', 'area_id' => null]);
                    $this->sendJobTypeQuickReply($replyToken, $session->gender);
                    return;
                }
                $area = Area::where('name', $text)->first();
                if (!$area) {
                    $this->replyWithQuickReplies($replyToken, "リストから選択してください。", $this->buildAreaItems());
                    return;
                }
                $session->update(['step' => 'job_type', 'area_id' => $area->id]);
                $this->sendJobTypeQuickReply($replyToken, $session->gender);
                break;

            case 'job_type':
                if ($text === 'なんでも') {
                    $this->saveAlert($session, null, $replyToken, $userId);
                    return;
                }
                $jobTypeQuery = JobType::where('name', $text);
                if ($session->gender !== 'both') {
                    $jobTypeQuery->where(fn($q) => $q->where('target_gender', $session->gender)->orWhere('target_gender', 'both'));
                }
                $jobType = $jobTypeQuery->first();
                if (!$jobType) {
                    $this->sendJobTypeQuickReply($replyToken, $session->gender);
                    return;
                }
                $this->saveAlert($session, $jobType->id, $replyToken, $userId);
                break;
        }
    }

    private function saveAlert(JobAlertSession $session, ?int $jobTypeId, string $replyToken, string $userId): void
    {
        $areaName    = $session->area ? $session->area->name : '全国';
        $genderLabel = match($session->gender) {
            'female' => '女性ナイトワーク',
            'male'   => '男性ナイトワーク',
            'both'   => '両方',
            default  => $session->gender,
        };
        $jobTypeName = $jobTypeId ? JobType::find($jobTypeId)?->name : 'なんでも';

        JobAlert::updateOrCreate(
            ['line_user_id' => $userId],
            [
                'gender'      => $session->gender,
                'area_id'     => $session->area_id,
                'job_type_id' => $jobTypeId,
                'is_active'   => true,
            ]
        );
        $session->delete();

        $this->reply($replyToken,
            "✅ 求人アラートを登録しました！\n\n"
            . "性別　：{$genderLabel}\n"
            . "エリア：{$areaName}\n"
            . "職種　：{$jobTypeName}\n\n"
            . "条件に合う求人が公開されたらお知らせします。\n"
            . "解除するには「解除」と送信してください。"
        );
    }

    private function deactivateAlert(string $userId, string $replyToken): void
    {
        $deleted = JobAlert::where('line_user_id', $userId)->where('is_active', true)->count();
        JobAlert::where('line_user_id', $userId)->update(['is_active' => false]);
        JobAlertSession::where('line_user_id', $userId)->delete();

        if ($deleted > 0) {
            $this->reply($replyToken, "求人アラートを解除しました。\n再登録はサイトの求人一覧ページから行えます。");
        } else {
            $this->reply($replyToken, "登録済みのアラートはありません。\nサイトの求人一覧ページから登録できます。");
        }
    }

    private function handleWebAlertToken(string $token, string $userId, string $replyToken): void
    {
        $webToken = WebAlertToken::with(['area', 'jobType'])->where('token', $token)->first();

        if (!$webToken || $webToken->isExpired()) {
            $this->reply($replyToken, "リンクの有効期限が切れています。\nもう一度サイトから設定してください。");
            return;
        }

        $genderLabel = match($webToken->gender) {
            'female' => '女性ナイトワーク',
            'male'   => '男性ナイトワーク',
            'both'   => '両方',
            default  => $webToken->gender,
        };
        $areaName    = $webToken->area?->name ?? '全国';
        $jobTypeName = $webToken->jobType?->name ?? 'なんでも';

        JobAlert::updateOrCreate(
            ['line_user_id' => $userId],
            [
                'gender'          => $webToken->gender,
                'area_id'         => $webToken->area_id,
                'job_type_id'     => $webToken->job_type_id,
                'daily_pay_ok'    => $webToken->daily_pay_ok,
                'inexperienced_ok'=> $webToken->inexperienced_ok,
                'arubaito'        => $webToken->arubaito,
                'is_active'       => true,
            ]
        );
        $webToken->delete();
        JobAlertSession::where('line_user_id', $userId)->delete();

        $conditions = array_filter([
            $webToken->daily_pay_ok     ? '日払いOK' : null,
            $webToken->inexperienced_ok ? '未経験歓迎' : null,
            $webToken->arubaito         ? 'アルバイト' : null,
        ]);
        $conditionText = $conditions ? "\n条件　　：" . implode('・', $conditions) : '';

        $this->reply($replyToken,
            "✅ 求人アラートを登録しました！\n\n"
            . "カテゴリ：{$genderLabel}\n"
            . "エリア　：{$areaName}\n"
            . "職種　　：{$jobTypeName}"
            . $conditionText . "\n\n"
            . "条件に合う求人が公開されたらお知らせします。\n"
            . "解除するには「解除」と送信してください。"
        );
    }

    // --------------------------------------------------------
    // 店舗オーナー用: NW-コード
    // --------------------------------------------------------
    private function handleShopCode(int $shopId, string $userId, string $replyToken): void
    {
        $shop = Shop::find($shopId);
        if (!$shop || $shop->status !== 'active') {
            $this->reply($replyToken,
                "店舗が見つかりませんでした。\n管理画面に表示されているコードを正確に送ってください。"
            );
            return;
        }

        $shop->update(['line_notify_user_id' => $userId]);
        Log::info("LINE通知登録: shop={$shop->id} user={$userId}");

        $this->reply($replyToken,
            "【{$shop->name}】\nLINE通知の設定が完了しました！\n\n応募があると、このLINEアカウントにお知らせします。"
        );
    }

    // --------------------------------------------------------
    // クイックリプライヘルパー
    // --------------------------------------------------------
    private function sendAreaQuickReply(string $replyToken): void
    {
        $this->replyWithQuickReplies($replyToken, "希望エリアを選択してください。", $this->buildAreaItems());
    }

    private function buildAreaItems(): array
    {
        $areas = Area::withCount(['jobs' => fn($q) => $q->where('status', 'active')])
            ->orderByDesc('jobs_count')
            ->limit(12)
            ->get(['id', 'name']);

        $items = $areas->map(fn($a) => ['label' => $a->name, 'text' => $a->name])->toArray();
        $items[] = ['label' => '全国', 'text' => '全国'];
        return $items;
    }

    private function sendJobTypeQuickReply(string $replyToken, ?string $gender): void
    {
        $query = JobType::orderBy('sort_order')->limit(12);
        if ($gender !== 'both') {
            $query->where(fn($q) => $q->where('target_gender', $gender)->orWhere('target_gender', 'both'));
        }
        $types = $query->get(['id', 'name']);

        $items = $types->map(fn($j) => ['label' => $j->name, 'text' => $j->name])->toArray();
        $items[] = ['label' => 'なんでも', 'text' => 'なんでも'];

        $this->replyWithQuickReplies($replyToken, "希望職種を選択してください。", $items);
    }

    private function replyWithQuickReplies(string $replyToken, string $text, array $items): void
    {
        $quickReplyItems = array_map(fn($item) => [
            'type'   => 'action',
            'action' => ['type' => 'message', 'label' => $item['label'], 'text' => $item['text']],
        ], $items);

        $token = config('services.line.messaging_token');
        if (!$token) return;

        try {
            $res = Http::withToken($token)->post('https://api.line.me/v2/bot/message/reply', [
                'replyToken' => $replyToken,
                'messages'   => [[
                    'type'       => 'text',
                    'text'       => $text,
                    'quickReply' => ['items' => $quickReplyItems],
                ]],
            ]);
            if (!$res->successful()) {
                Log::error("LINE quickReply 失敗: HTTP " . $res->status() . " - " . $res->body());
            }
        } catch (\Exception $e) {
            Log::error("LINE quickReply 失敗: " . $e->getMessage());
        }
    }

    private function reply(string $replyToken, string $text): void
    {
        $token = config('services.line.messaging_token');
        if (!$token) return;

        try {
            $res = Http::withToken($token)->post('https://api.line.me/v2/bot/message/reply', [
                'replyToken' => $replyToken,
                'messages'   => [['type' => 'text', 'text' => $text]],
            ]);
            if (!$res->successful()) {
                Log::error("LINE reply 失敗: HTTP " . $res->status() . " - " . $res->body());
            }
        } catch (\Exception $e) {
            Log::error("LINE reply 失敗: " . $e->getMessage());
        }
    }
}
