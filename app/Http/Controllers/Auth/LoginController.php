<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use App\Mail\AdminLoginAlertMail;
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

        $ip          = $request->ip();
        $allowedIps  = config('admin.allowed_ips', []);
        $isAllowedIp = empty($allowedIps) || in_array($ip, $allowedIps);

        // 許可IPはレートリミット対象外
        if (!$isAllowedIp && RateLimiter::tooManyAttempts($throttleKey, 5)) {
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
            LoginLog::create(['user_id' => $user->id, 'ip_address' => $ip, 'user_agent' => $request->userAgent()]);

            if ($user->isAdmin()) {
                // 許可外IPからの成功ログインのみ通知（正常ログインは通知しない）
                if (!$isAllowedIp) {
                    Mail::to(config('mail.admin_address'))->queue(new AdminLoginAlertMail($ip, false, true));
                }
            } else {
            }

            $destination = $user->isAdmin()
                ? route('admin.dashboard')
                : ($user->isPartner()
                    ? route('manage.partner.index')
                    : ($user->role === 'visitor' ? route('user.dashboard') : route('manage.dashboard')));
            return redirect()->intended($destination);
        }

        RateLimiter::hit($throttleKey, 900);

        // ログイン失敗：管理者メールアドレスと一致する場合のみ通知
        $targetUser = User::where('email', $credentials['email'])->first();
        if ($targetUser?->isAdmin()) {
            Mail::to(config('mail.admin_address'))->queue(new AdminLoginAlertMail($ip, true, !$isAllowedIp));
        }

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
