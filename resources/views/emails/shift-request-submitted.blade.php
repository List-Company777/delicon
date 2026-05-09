{{ $cast->shop->name ?? '店舗' }} 御中

{{ $cast->name }} からシフト申請が届きました。

━━ 申請内容 ━━━━━━━━━━━━━━━
女性名　：{{ $cast->name }}
申請日　：{{ $shiftRequest->work_date->format('Y年m月d日(') }}{{ ['日','月','火','水','木','金','土'][$shiftRequest->work_date->dayOfWeek] }}）
@if($shiftRequest->start_time || $shiftRequest->end_time)
時間　　：{{ substr($shiftRequest->start_time ?? '', 0, 5) }}〜{{ substr($shiftRequest->end_time ?? '', 0, 5) }}
@endif
@if($shiftRequest->note)
メモ　　：{{ $shiftRequest->note }}
@endif
━━━━━━━━━━━━━━━━━━━━━━━

管理画面でシフト申請を確認・承認:
{{ route('manage.shift-requests.index') }}/

---
デリヘルリスト
https://delicon.jp/
