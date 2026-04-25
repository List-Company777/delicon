{{ $application->applicant_name }} 様

{{ $application->shop->name }} よりメッセージが届きました。

━━ メッセージ ━━━━━━━━━━━━━━━
{{ $applicationMessage->body }}
━━━━━━━━━━━━━━━━━━━━━━━

▼ 返信はこちらのリンクから（ログイン不要）
{{ route('apply.thread', $application->reply_token) }}

※ このメールに直接返信しても届きません。
  必ず上のリンクからご返信ください。

---
ナイトワークリスト
https://nightwork-list.com/
