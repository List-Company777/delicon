{{ $shop->name }} 様

有料プランの審査が完了し、クリック予算が追加されました。

■ 店舗名：{{ $shop->name }}
■ 追加金額：{{ number_format($amount) }}円
■ 現在の残高：{{ number_format($shop->budget_balance) }}円
■ 入札単価：{{ number_format($bidPrice) }}円

残高がなくなると入札単価が10円に戻ります。残高は管理画面で確認できます。

{{ config('app.url') }}/manage/dashboard/

---
ナイトワークリスト
