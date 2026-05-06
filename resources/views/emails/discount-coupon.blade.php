<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><title>割引クーポンのお知らせ</title></head>
<body style="background:#f5f5f5;font-family:sans-serif;margin:0;padding:20px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;">
    <div style="background:#1a0a0a;padding:24px;text-align:center;">
        <p style="color:#c8a450;font-size:22px;font-weight:bold;margin:0;">割引クーポンのお知らせ</p>
        <p style="color:#aaa;font-size:13px;margin:6px 0 0;">{{ $coupon->shop->name }}</p>
    </div>
    <div style="padding:28px 32px;">
        <p style="color:#333;margin-bottom:16px;">{{ $coupon->user->name }} 様</p>
        <p style="color:#333;margin-bottom:20px;">この度は口コミをご投稿いただきありがとうございます。<br>感謝の気持ちとして、割引クーポンをお送りします。</p>

        @if($coupon->message)
        <div style="background:#fafafa;border-left:3px solid #c02040;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#555;">
            {{ $coupon->message }}
        </div>
        @endif

        <div style="background:#1a0a0a;border-radius:8px;padding:24px;text-align:center;margin-bottom:20px;">
            <p style="color:#c8a450;font-size:11px;letter-spacing:2px;margin:0 0 8px;">クーポンコード</p>
            <p style="color:#fff;font-size:28px;font-weight:bold;letter-spacing:4px;font-family:monospace;margin:0 0 8px;">{{ $coupon->code }}</p>
            <p style="color:#c02040;font-size:20px;font-weight:bold;margin:0;">¥{{ number_format($coupon->discount_amount) }} 割引</p>
        </div>

        <table style="width:100%;font-size:13px;border-collapse:collapse;margin-bottom:20px;">
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:8px 0;color:#888;width:40%;">有効期限</td>
                <td style="padding:8px 0;color:#333;font-weight:bold;">{{ $coupon->expires_at->format('Y年m月d日') }}</td>
            </tr>
            @if($coupon->min_order_amount)
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:8px 0;color:#888;">最低利用金額</td>
                <td style="padding:8px 0;color:#333;">¥{{ number_format($coupon->min_order_amount) }} 以上</td>
            </tr>
            @endif
            @if($coupon->conditions)
            <tr>
                <td style="padding:8px 0;color:#888;">適用条件</td>
                <td style="padding:8px 0;color:#333;">{{ $coupon->conditions }}</td>
            </tr>
            @endif
        </table>

        <p style="color:#888;font-size:12px;">ご予約・ご利用の際に上記のコードをお申し付けください。<br>本クーポンは{{ $coupon->user->name }}様専用のコードです。譲渡・転売はできません。</p>
    </div>
    <div style="background:#f5f5f5;padding:16px 32px;text-align:center;font-size:11px;color:#aaa;">
        <p style="margin:0;">{{ $coupon->shop->name }} | {{ config('app.name') }}</p>
    </div>
</div>
</body>
</html>
