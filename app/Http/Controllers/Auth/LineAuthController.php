<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class LineAuthController extends Controller
{
    // LINEの認証画面へリダイレクト（ログイン・新規登録用）
    public function redirect()
    {
        return Socialite::driver('line')->redirect();
    }

    // LINE連携（ログイン済みユーザーが後から連携する用）
    public function connectRedirect()
    {
        session(['line_intent' => 'connect']);
        return Socialite::driver('line')->redirect();
    }

    // LINEからのコールバック
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            $intent = session()->pull('line_intent');
            $redirect = $intent === 'connect' ? route('manage.password.edit') : route('login');
            return redirect($redirect)->withErrors(['line' => 'LINE認証がキャンセルされました']);
        }

        $lineUser = Socialite::driver('line')->user();

        // 連携モード：ログイン済みユーザーのline_user_idを更新
        if (session()->pull('line_intent') === 'connect' && Auth::check()) {
            $current = Auth::user();
            // 他のアカウントに紐づいていないか確認
            $conflict = User::where('line_user_id', $lineUser->getId())
                ->where('id', '!=', $current->id)
                ->exists();
            if ($conflict) {
                return redirect(route('manage.password.edit'))
                    ->withErrors(['line' => 'このLINEアカウントはすでに別のアカウントに連携されています']);
            }
            $current->update(['line_user_id' => $lineUser->getId()]);
            return redirect(route('manage.password.edit'))->with('success', 'LINEアカウントを連携しました');
        }

        // 既存ユーザーの照合（LINEログイン）
        $user = User::where('line_user_id', $lineUser->getId())->first();

        if ($user) {
            Auth::login($user, true);
            request()->session()->regenerate();
            $user->timestamps = false;
            $user->update(['last_login_at' => now()]);
            return redirect()->intended(route('manage.dashboard'));
        }

        // 新規ユーザー → 登録ステップへ（LINEデータをセッションに保持）
        session([
            'line_user_id' => $lineUser->getId(),
            'line_name'    => $lineUser->getName(),
        ]);

        return redirect(route('register'));
    }
}
