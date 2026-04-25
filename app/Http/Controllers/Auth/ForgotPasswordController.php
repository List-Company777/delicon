<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function show()
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        // メールアドレスの存在を漏らさないため、常に同じメッセージを返す
        return back()->with('status', '入力したメールアドレスにパスワード再設定メールを送信しました（登録済みの場合）。');
    }
}
