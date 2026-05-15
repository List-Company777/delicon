@if($isFailed)
管理者アカウントへの不正なログイン試行を検知しました。
@else
許可されていないIPアドレスからの管理者ログイン試行を検知しました。
@endif

日時：{{ now()->format('Y年m月d日 H:i') }}
IPアドレス：{{ $ipAddress }}
@if($isFailed)
種別：パスワード認証失敗
@endif
@if($isUnknownIp)
種別：許可リスト外のIPアドレス
@endif

心当たりのない場合は、すぐにパスワードを変更し、管理画面を確認してください。

デリコン 運営事務局
