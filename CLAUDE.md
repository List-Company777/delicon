# delicon プロジェクト情報

## 概要
デリヘル求人サイト「デリヘルリスト」（旧デリコンがリニューアル）

## サーバー
- IP: 45.76.223.63（Vultr）
- SSH: `ssh admin@45.76.223.63`
- パス: `/var/www/delicon`
- DB: `delicon`（MariaDB）/ Redis
- URL: https://delicon.jp

## デプロイ手順
```bash
git pull
php artisan view:clear && php artisan cache:clear && php artisan config:clear && php artisan route:clear
sudo systemctl reload php8.3-fpm
```

## 技術的注意点（重要）
- CSP nonce: `Vite::cspNonce()` を使う（`app('csp-nonce')` は500になる）
- インライン属性（`onchange=""` `onclick=""` など）はCSPでブロックされる → 必ず `addEventListener` を使う
- ブランド名: 「デリヘルリスト」（ドメインはdelicon.jpだがサイト名はデリヘルリスト）
- ログインURL: `https://delicon.jp/login/`（`/shops/login` は旧URLで無効）

## 課金・ランキング
- 継続先着順（`plan{N}_since`）/ 月次サイクル（20日受付）
- ランキングスコア: 電話×10・お気に入り×3・口コミ×5・閲覧×1・プランボーナス(0/15/30/50)

## GitHub
`git@github-delicon:List-Company777/delicon.git`


## ターゲットユーザー層

**メインユーザー**: 20〜40代男性（デリヘル店を探す・比較するユーザー）
**サブユーザー**: デリヘルキャスト志望の女性 / 店舗オーナー
**優先デバイス**: スマホ

### 設計に反映すること
- 電話・料金・エリアの情報を最上位に表示する
- キャスト詳細ページは写真・スタイル・シフトを充実させると集客に直結する
- 店舗側管理画面は「審査→掲載→応募管理」のフローを明確に示す
