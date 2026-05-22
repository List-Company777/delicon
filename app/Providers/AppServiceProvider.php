<?php

namespace App\Providers;

use App\Models\Area;
use App\Models\Cast;
use App\Models\Shop;
use App\Observers\CastObserver;
use App\Observers\ShopObserver;
use App\Models\Job;
use App\Models\Prefecture;
use App\Observers\JobObserver;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $allowedIps = config('admin.allowed_ips', []);
            if (!empty($allowedIps) && in_array($request->ip(), $allowedIps)) {
                return Limit::none();
            }
            return Limit::perMinutes(15, 5)->by($request->ip());
        });

        Blade::directive('nonce', fn() => '<?php echo \'nonce="\' . e(Vite::cspNonce()) . \'"\'; ?>');

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        $replyTo = config("mail.reply_to");
        if (!empty($replyTo["address"])) {
            Mail::alwaysReplyTo($replyTo["address"], $replyTo["name"]);
        }

        Cast::observe(CastObserver::class);
        Shop::observe(ShopObserver::class);
        Job::observe(JobObserver::class);

        // admin レイアウト全体に都道府県・エリアリストを共有（キャッシュなし）
        View::composer('layouts.admin', function ($view) {
            $view->with('prefectures', Prefecture::orderBy('id')->get());
            $view->with('allAreas', Area::with('prefecture')->orderBy('prefecture_id')->orderBy('sort_order')->get());
        });

        // ログイン済みで /login/ にアクセスした場合、ロールに応じてダッシュボードへ
        RedirectIfAuthenticated::redirectUsing(function () {
            $user = Auth::user();
            if ($user && $user->isAdmin()) {
                return route('admin.dashboard');
            }
            return route('manage.dashboard');
        });

        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('line', \SocialiteProviders\Line\Provider::class);
        });
    }
}
