<?php

namespace App\Providers;

use App\Models\Area;
use App\Models\Job;
use App\Models\Prefecture;
use App\Observers\JobObserver;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Job::observe(JobObserver::class);

        // admin レイアウト全体に都道府県リストを共有
        View::composer('layouts.admin', function ($view) {
            $view->with('prefectures', Prefecture::orderBy('sort_order')->get());
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
