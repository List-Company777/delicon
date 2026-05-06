<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class VisitorRegisterController extends Controller
{
    public function show(Request $request)
    {
        $redirect = $request->query('redirect');
        return view('auth.visitor-register', compact('redirect'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:50'],
            'email'    => ['required', 'email', 'max:200', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required'     => 'ニックネームを入力してください。',
            'email.required'    => 'メールアドレスを入力してください。',
            'email.unique'      => 'このメールアドレスはすでに登録されています。',
            'password.required' => 'パスワードを入力してください。',
            'password.confirmed'=> 'パスワードが一致していません。',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'role'     => 'visitor',
            'email_verified_at' => now(), // メール確認不要（簡易登録）
        ]);

        Auth::login($user);

        $redirect = $request->input('redirect');
        return redirect($redirect ?: route('top'))->with('success', '登録が完了しました。口コミを投稿できます。');
    }
}
