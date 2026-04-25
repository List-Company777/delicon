<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, follow">
    <title>メンテナンス中 | ナイトワークリスト</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', Meiryo, sans-serif; background: #f9fafb; color: #374151; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem 1rem; }
        .card { background: #fff; border-radius: 1rem; padding: 2.5rem 2rem; max-width: 480px; width: 100%; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .icon { font-size: 3rem; margin-bottom: 1rem; }
        h1 { font-size: 1.25rem; font-weight: 700; color: #374151; margin-bottom: .75rem; }
        p { font-size: .875rem; color: #6b7280; line-height: 1.6; }
        footer { margin-top: 2rem; font-size: .75rem; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="card">
        <p class="icon">🔧</p>
        <h1>メンテナンス中です</h1>
        <p>
            現在、システムメンテナンスのためご利用いただけません。<br>
            しばらく時間をおいてから再度アクセスしてください。<br><br>
            ご不便をおかけして申し訳ありません。
        </p>
    </div>
    <footer>&copy; {{ date('Y') }} ナイトワークリスト</footer>
</body>
</html>
