<?php

namespace App\Providers;

use App\Models\Area;
use App\Models\Cast;
use App\Observers\CastObserver;
use App\Models\Job;
use App\Models\Prefecture;
use App\Observers\JobObserver;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
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
        Blade::directive('nonce', fn() => '<?php echo \'nonce="\' . e(Vite::cspNonce()) . \'"\'; ?>');

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Cast::observe(CastObserver::class);
        Job::observe(JobObserver::class);

        // admin レイアウト全体に都道府県リストを共有
        View::composer('layouts.admin', function ($view) {
            $view->with('prefectures', Prefecture::orderBy("id")->get());
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
