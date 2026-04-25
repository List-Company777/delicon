<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
}
