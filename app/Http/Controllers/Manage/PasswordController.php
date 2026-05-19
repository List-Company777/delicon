<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PasswordController extends Controller
{
    public function edit()
    {
        return view('manage.password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => '現在のパスワードを入力してください',
            'password.required'         => '新しいパスワードを入力してください',
            'password.min'              => 'パスワードは8文字以上で入力してください',
            'password.confirmed'        => 'パスワード（確認）が一致しません',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => '現在のパスワードが正しくありません']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'パスワードを変更しました');
    }

    public function updateEmail(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'email.required' => 'メールアドレスを入力してください',
            'email.email'    => '正しいメールアドレスの形式で入力してください',
            'email.unique'   => 'このメールアドレスはすでに使用されています',
        ]);

        $user->update([
            'email'              => $request->email,
            'email_verified_at'  => null,
            'email_bounced_at'   => null,
        ]);

        $user->sendEmailVerificationNotification();

        return back()->with('email_success', 'メールアドレスを変更しました。確認メールを送信しましたので認証をお願いします。');
    }
}
