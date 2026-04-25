{{ $application->applicant_name }} 様（{{ $application->applicant_email }}）から返信が届きました。

求人：{{ $application->job->title ?? '（求人削除済み）' }}

━━ メッセージ ━━━━━━━━━━━━━━━
{{ $applicationMessage->body }}
━━━━━━━━━━━━━━━━━━━━━━━

管理画面でスレッドを確認:
{{ route('manage.applications.show', $application->id) }}

---
ナイトワークリスト
https://nightwork-list.com/
