<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><title>口コミへの返信</title></head>
<body style="background:#1a1a2e;color:#e8e4dc;font-family:sans-serif;padding:20px;max-width:600px;margin:0 auto">
<div style="background:#252535;border-radius:12px;padding:24px;border:1px solid #3a3a4a">
  <h2 style="color:#e8899a;margin-top:0">💬 口コミへの返信が届きました</h2>
  <p>{{ $review->user?->name ?? $review->nickname ?? 'お客様' }} 様</p>
  <p>投稿した口コミに <strong>{{ $review->cast?->shop?->name ?? '店舗' }}</strong> から返信がありました。</p>

  <div style="background:#1a1a2e;border-radius:8px;padding:16px;margin:16px 0;border-left:3px solid #e8899a">
    <p style="margin:0 0 4px;font-size:12px;color:#8a8a9e">あなたの口コミ（{{ $review->cast?->name }}・★{{ $review->rating }}）</p>
    <p style="margin:0;font-size:13px;color:#b0aead">{{ mb_substr($review->body, 0, 100) }}{{ mb_strlen($review->body) > 100 ? '…' : '' }}</p>
  </div>

  <div style="background:#2a2535;border-radius:8px;padding:16px;margin:16px 0;border-left:3px solid #c8a450">
    <p style="margin:0 0 4px;font-size:12px;color:#8a8a9e">店舗からの返信</p>
    <p style="margin:0;font-size:14px;color:#e8e4dc;white-space:pre-wrap">{{ $review->shop_reply }}</p>
  </div>

  <a href="{{ route('cast.show', $review->cast_id) }}/"
     style="display:inline-block;background:#e8899a;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold">
    ページで確認する
  </a>
  <p style="color:#6a6a7e;font-size:12px;margin-top:24px">デリヘルリスト</p>
</div>
</body></html>
