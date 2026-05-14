{{ $job->shop->name }} 御中

新しい応募が届きました。

━━ 応募者情報 ━━━━━━━━━━━━━━━
お名前　：{{ $application->applicant_name }}
メール　：{{ $application->applicant_email }}
@if($application->applicant_age)
年齢　　：{{ $application->applicant_age }}歳
@endif
@if($application->applicant_tel)
電話番号：{{ $application->applicant_tel }}
@endif
━━━━━━━━━━━━━━━━━━━━━━━

━━ 求人情報 ━━━━━━━━━━━━━━━
求人名　：{{ $job->title }}
店舗名　：{{ $job->shop->name }}
@if($job->shop->address)
住所　　：{{ $job->shop->address }}
地図　　：https://maps.google.com/?q={{ urlencode($job->shop->address) }}
@endif
@if($job->shop->nearest_station_name)
最寄り駅：{{ $job->shop->nearest_line ? $job->shop->nearest_line . ' ' : '' }}{{ $job->shop->nearest_station_name }}駅{{ $job->shop->nearest_station_walk ? ' 徒歩' . $job->shop->nearest_station_walk . '分' : '' }}
@endif
@if($job->shop->detail?->opening_hours)
営業開始：{{ $job->shop->detail->opening_hours }}
@endif
応募職種：{{ $job->jobType?->name ?? '不明' }}{{ $job->employment_type ? '（' . ['PART_TIME'=>'アルバイト','CONTRACTOR'=>'業務委託','FULL_TIME'=>'正社員','PER_DIEM'=>'日払い','OTHER'=>'その他'][$job->employment_type] . '）' : '' }}
━━━━━━━━━━━━━━━━━━━━━━━
@if($application->message)

メッセージ：
{{ $application->message }}
@endif

管理画面で応募を確認:
{{ route('manage.applications.show', $application->id) }}/

※ 初めてログインする方はパスワードの再設定が必要です:
{{ route('password.request') }}/

---
デリヘルリスト
https://delicon.jp/
