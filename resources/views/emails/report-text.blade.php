ナイトワークリスト 通報通知
=====================================

【通報種別】
{{ $targetType === 'shop' ? '営業ページ' : '求人ページ' }}

【店舗情報】
店舗ID  : {{ $shopId }}
店舗名  : {{ $shopName }}
@if($targetType === 'job')

【求人情報】
求人ID  : {{ $targetId }}
求人タイトル: {{ $targetName }}
@endif

【通報理由】
{{ $reason }}

【詳細コメント】
{{ $comment ?: '（なし）' }}

【報告者メール】
{{ $reporterEmail ?: '（未入力）' }}

【IPアドレス】
{{ $reporterIp }}

=====================================
管理画面: {{ config('app.url') }}/admin/shops/{{ $shopId }}/
