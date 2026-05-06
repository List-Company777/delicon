<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><title>出勤通知</title></head>
<body style="background:#1a1a2e;color:#e8e4dc;font-family:sans-serif;padding:20px;max-width:600px;margin:0 auto">
<div style="background:#252535;border-radius:12px;padding:24px;border:1px solid #3a3a4a">
  <h2 style="color:#e8899a;margin-top:0">💗 お気に入りの女性が出勤します</h2>
  <p>{{ $user->name }} 様</p>
  <p>お気に入り登録している <strong>{{ $cast->name }}</strong> さんが本日出勤予定です。</p>
  <div style="background:#1a1a2e;border-radius:8px;padding:16px;margin:16px 0">
    <p style="margin:0;font-size:18px;font-weight:bold">{{ $cast->name }}</p>
    @if($cast->age)<p style="margin:4px 0;color:#8a8a9e;font-size:14px">{{ $cast->age }}歳</p>@endif
    @if($cast->shop)<p style="margin:4px 0;color:#8a8a9e;font-size:14px">{{ $cast->shop->name }}</p>@endif
  </div>
  <a href="{{ route('cast.show', $cast->id) }}/"
     style="display:inline-block;background:#e8899a;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold">
    プロフィールを見る
  </a>
  <p style="color:#6a6a7e;font-size:12px;margin-top:24px">
    出勤通知を受け取りたくない場合は <a href="{{ route('user.settings') }}/" style="color:#e8899a">通知設定</a> から変更できます。
  </p>
</div>
</body></html>
