<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><title>クーポン期限のお知らせ</title></head>
<body style="background:#1a1a2e;color:#e8e4dc;font-family:sans-serif;padding:20px;max-width:600px;margin:0 auto">
<div style="background:#252535;border-radius:12px;padding:24px;border:1px solid #3a3a4a">
  <h2 style="color:#c8a450;margin-top:0">🎟 クーポンの期限が近づいています</h2>
  <p>{{ $coupon->user?->name ?? 'お客様' }} 様</p>
  <p><strong>{{ $coupon->shop?->name }}</strong> からいただいた割引クーポンの有効期限まであと3日です。</p>

  <div style="background:#1a0a0a;border-radius:8px;padding:24px;text-align:center;margin:16px 0">
    <p style="color:#c8a450;font-size:11px;letter-spacing:2px;margin:0 0 8px">クーポンコード</p>
    <p style="color:#fff;font-size:28px;font-weight:bold;letter-spacing:4px;font-family:monospace;margin:0 0 8px">{{ $coupon->code }}</p>
    <p style="color:#c02040;font-size:20px;font-weight:bold;margin:0">¥{{ number_format($coupon->discount_amount) }} 割引</p>
    <p style="color:#aaa;font-size:13px;margin:8px 0 0">有効期限：<strong style="color:#f59e0b">{{ $coupon->expires_at->format('Y年m月d日') }}</strong></p>
  </div>

  @if($coupon->message)
  <div style="background:#2a2535;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#c8c4bc">
    {{ $coupon->message }}
  </div>
  @endif

  <p style="color:#8a8a9e;font-size:13px">ご予約・ご利用の際に、<strong style="color:#e8e4dc">サイトに登録したお名前</strong>をスタッフにお伝えください。</p>

  <a href="{{ route('user.coupons') }}/"
     style="display:inline-block;background:#c8a450;color:#1a0a0a;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold">
    クーポン一覧を見る
  </a>
  <p style="color:#6a6a7e;font-size:12px;margin-top:24px">デリヘルリスト</p>
</div>
</body></html>
