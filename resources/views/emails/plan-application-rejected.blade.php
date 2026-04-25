{{ $shop->name }} 様

この度は有料プランへのお申し込みをいただき、ありがとうございました。

誠に恐れ入りますが、今回のお申し込みについては、ご希望に添いかねる結果となりました。

■ 店舗名：{{ $shop->name }}
■ 申込金額：{{ number_format($amount) }}円
■ 希望入札単価：{{ number_format($bidPrice) }}円
@if($note)

■ 備考：{{ $note }}
@endif

引き続き基本プランにてご利用いただけます。
ご不明な点がございましたら、管理画面のお問い合わせよりご連絡ください。

{{ config('app.url') }}/manage/dashboard/

---
ナイトワークリスト
