<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><title>新しい口コミ</title></head>
<body style="background:#f5f5f5;font-family:sans-serif;padding:20px;max-width:600px;margin:0 auto">
<div style="background:#fff;border-radius:12px;padding:24px;border:1px solid #e5e7eb">
  <h2 style="color:#c02040;margin-top:0">📝 新しい口コミが投稿されました</h2>
  <p style="color:#374151">{{ $review->cast?->shop?->name ?? '店舗' }} 様</p>
  <p style="color:#374151"><strong>{{ $review->cast?->name }}</strong> に新しい口コミが投稿されました。</p>

  <div style="background:#fafafa;border-radius:8px;padding:16px;margin:16px 0;border:1px solid #e5e7eb">
    <p style="margin:0 0 6px;font-size:13px;color:#6b7280">
      ★{{ $review->rating }} &nbsp;|&nbsp; {{ $review->nickname ?? '匿名' }} &nbsp;|&nbsp; {{ $review->created_at->format('Y/m/d') }}
    </p>
    <p style="margin:0;font-size:14px;color:#374151;line-height:1.6">{{ mb_substr($review->body, 0, 200) }}{{ mb_strlen($review->body) > 200 ? '…' : '' }}</p>
  </div>

  <a href="{{ route('manage.review.index') }}/"
     style="display:inline-block;background:#c02040;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold">
    管理画面で確認・返信する
  </a>
  <p style="color:#9ca3af;font-size:12px;margin-top:24px">デリヘルリスト 管理画面 | <a href="{{ route('manage.review.index') }}/" style="color:#c02040">口コミ管理</a></p>
</div>
</body></html>
