<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, follow">
    <title>サーバーエラー | ナイトワークリスト</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', Meiryo, sans-serif; background: #f9fafb; color: #374151; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem 1rem; }
        .card { background: #fff; border-radius: 1rem; padding: 2.5rem 2rem; max-width: 480px; width: 100%; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .code { font-size: 4rem; font-weight: 800; color: #e5a000; line-height: 1; margin-bottom: .75rem; }
        h1 { font-size: 1.25rem; font-weight: 700; color: #374151; margin-bottom: .75rem; }
        p { font-size: .875rem; color: #6b7280; line-height: 1.6; margin-bottom: 2rem; }
        .links { display: flex; flex-direction: column; gap: .75rem; }
        a.btn { display: block; padding: .75rem 1.5rem; border-radius: .75rem; font-size: .875rem; font-weight: 700; text-decoration: none; transition: opacity .15s; }
        a.btn:hover { opacity: .85; }
        .btn-dark { background: #1f2937; color: #fff; }
        .btn-female { background: #fdf2f8; color: #9d174d; border: 1px solid #fbcfe8; }
        .btn-male   { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .btn-biz    { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        footer { margin-top: 2rem; font-size: .75rem; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="card">
        <p class="code">500</p>
        <h1>サーバーエラーが発生しました</h1>
        <p>
            申し訳ありません。一時的なエラーが発生しています。<br>
            しばらく時間をおいてから再度お試しください。<br>
            問題が続く場合はお問い合わせください。
        </p>
        <div class="links">
            <a href="/" class="btn btn-dark">トップページへ戻る</a>
            <a href="/female/all/all/" class="btn btn-female">👩 女性ナイトワーク</a>
            <a href="/male/all/all/" class="btn btn-male">👨 男性ナイトワーク</a>
            <a href="/yoasobi/all/all/" class="btn btn-biz">🍸 夜遊び情報</a>
        </div>
    </div>
    <footer>&copy; {{ date('Y') }} ナイトワークリスト</footer>
</body>
</html>
