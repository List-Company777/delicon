デリコン 週次レポート（{{ now()->subDay()->format('Y年m月d日') }} 木曜時点）
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

■ ユーザー（一般）
  総登録数          {{ number_format($stats['users_total']) }} 人
  今週の新規登録    {{ number_format($stats['users_week']) }} 人
  店舗アカウント    {{ number_format($stats['shop_accounts']) }} 件（別計上）

■ プッシュ通知（木曜18:00送信）
  購読者数          {{ number_format($stats['push_subs']) }} 人
  送信数            {{ $stats['push_sent'] !== null ? number_format($stats['push_sent']) . ' 件' : '送信記録なし' }}
  失敗・削除        {{ $stats['push_sent'] !== null ? number_format($stats['push_failed']) . ' 件' : '—' }}

■ 店舗・キャスト
  掲載中の店舗数    {{ number_format($stats['active_shops']) }} 店
  掲載中のキャスト  {{ number_format($stats['active_casts']) }} 人
  今週の新規店舗    {{ number_format($stats['new_shops_week']) }} 店

■ エンゲージメント（今週）
  キャスト閲覧数    {{ number_format($stats['cast_views_week']) }} PV
  お気に入り登録    {{ number_format($stats['favorites_week']) }} 件（累計: {{ number_format($stats['favorites_total']) }}）
  新着通知設定      {{ number_format($stats['shop_notify_total']) }} 件（店舗フォロー）
  口コミ投稿        {{ number_format($stats['reviews_week']) }} 件

■ プラン申し込み（今週）
  申し込み件数      {{ number_format($stats['plan_apps_week']) }} 件
  承認待ち          {{ number_format($stats['plan_apps_pending']) }} 件

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
デリコン管理画面: https://delicon.jp/admin/
