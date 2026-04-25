<x-mail::message>
# 店舗管理画面からのお問い合わせ

**店舗名：** {{ $shopName }}
**送信者：** {{ $senderName }}（{{ $senderEmail }}）
**カテゴリ：** {{ $category }}
**件名：** {{ $contactSubject }}

---

{!! nl2br(e($body)) !!}

---

*このメールは nightwork-list.com 店舗管理画面から自動送信されました。*
*返信先は送信者メールアドレスに設定されています。*
</x-mail::message>
