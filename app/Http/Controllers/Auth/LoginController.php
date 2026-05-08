<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminLoginAlertMail;
use App\Mail\UserLoginAlertMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $minutes = ceil(RateLimiter::availableIn($throttleKey) / 60);
            return back()->withErrors([
                'email' => "ログイン試行回数の上限に達しました。{$minutes}分後に再試行してください。",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            $user = Auth::user();
            $user->timestamps = false;
            $user->update(['last_login_at' => now()]);

            if ($user->isAdmin()) {
                Mail::to(config('mail.admin_address'))->queue(new AdminLoginAlertMail($request->ip()));
            } else {
                Mail::to($user->email)->queue(new UserLoginAlertMail($request->ip()));
            }

            $destination = $user->isAdmin()
                ? route('admin.dashboard')
                : ($user->isPartner()
                    ? route('manage.partner.index')
                    : ($user->role === 'visitor' ? route('user.dashboard') : route('manage.dashboard')));
            return redirect()->intended($destination);
        }

        RateLimiter::hit($throttleKey, 900);
        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('login'));
    }
}
