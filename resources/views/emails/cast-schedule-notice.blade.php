<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><title>シフト通知</title></head>
<body style="background:#1a1a2e;color:#e8e4dc;font-family:sans-serif;padding:20px;max-width:600px;margin:0 auto">
<div style="background:#252535;border-radius:12px;padding:24px;border:1px solid #3a3a4a">
  <h2 style="color:#e8899a;margin-top:0">💗 お気に入りのキャストのシフトが入りました</h2>
  <p>{{ $user->name }} 様</p>
  <p>お気に入り登録しているキャストのシフトが登録されました。</p>

  @foreach($items as $item)
  <div style="background:#1a1a2e;border-radius:8px;padding:14px 16px;margin:10px 0;display:flex;align-items:center;gap:16px">
    <div style="flex:1">
      <p style="margin:0;font-size:16px;font-weight:bold">{{ $item['cast']->name }}</p>
      @if($item['cast']->shop)
      <p style="margin:2px 0 0;color:#8a8a9e;font-size:12px">{{ $item['cast']->shop->name }}</p>
      @endif
      <p style="margin:4px 0 0;color:#e8899a;font-size:13px">{{ implode('・', $item['dates']) }} 出勤予定</p>
    </div>
    <a href="{{ route('cast.show', $item['cast']->id) }}/"
       style="display:inline-block;background:#e8899a;color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:bold;white-space:nowrap">
      詳細を見る
    </a>
  </div>
  @endforeach

  @if(count($items) >= 10)
  <p style="color:#8a8a9e;font-size:12px;margin-top:12px">※ 10名を超える場合はマイページでご確認ください。</p>
  <a href="{{ route('user.dashboard') }}/"
     style="display:inline-block;background:#3a3a4a;color:#e8e4dc;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:12px">
    マイページを見る
  </a>
  @endif

  <p style="color:#6a6a7e;font-size:12px;margin-top:24px">
    通知を受け取りたくない場合は <a href="{{ route('user.dashboard') }}/" style="color:#e8899a">マイページ</a> の「お気に入り出勤通知を受け取る」のチェックを外してください。
  </p>
</div>
</body></html>
