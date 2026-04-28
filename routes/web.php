<?php

use App\Http\Controllers\TopController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LineAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Manage\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\KeywordController as AdminKeyword;
use App\Http\Controllers\Admin\MasterController as AdminMaster;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// サイトマップ（静的ファイルより先にルートで処理）
Route::get('/sitemap.xml',        [SitemapController::class, 'main']);
Route::get('/sitemap-detail.xml', [SitemapController::class, 'detail']);
Route::get('/sitemap-pages.xml',  [SitemapController::class, 'pages']);

// トップページ
Route::get('/', [TopController::class, 'index'])->name('top');

// 認証（ゲストのみ）
Route::middleware('guest')->group(function () {
    Route::get('/login/',    [LoginController::class, 'show'])->name('login');
    Route::post('/login/',   [LoginController::class, 'store'])->middleware('throttle:20,1');
    Route::get('/register/', [RegisterController::class, 'show'])->name('register');
    Route::post('/register/', [RegisterController::class, 'store']);
    // www.up-stage.info連携店舗の引き継ぎ検索（登録フォームのAJAX）
    Route::get('/api/xml-shops/search/', [RegisterController::class, 'xmlShopSearch'])->name('api.xml-shops.search');
    Route::get('/auth/line/',          [LineAuthController::class, 'redirect'])->name('auth.line');
    Route::get('/auth/line/callback/', [LineAuthController::class, 'callback'])->name('auth.line.callback');
    Route::get('/auth/line/connect/',  [LineAuthController::class, 'connectRedirect'])->middleware('auth')->name('auth.line.connect');
    // パスワードリセット
    Route::get('/forgot-password/',        [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password/',       [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'send'])->name('password.email');
    Route::get('/reset-password/{token}/', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password/',        [\App\Http\Controllers\Auth\ResetPasswordController::class, 'update'])->name('password.store');
});

// ログアウト
Route::post('/logout/', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// メール認証
Route::middleware('auth')->group(function () {
    Route::get('/email/verify/', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}/', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('manage.dashboard');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification/', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('resent', true);
    })->middleware('throttle:6,1')->name('verification.send');
});

// 法的ページ・会社情報
Route::view('/privacy/',    'legal.privacy')->name('privacy');
Route::view('/terms/',      'legal.terms')->name('terms');
Route::view('/advertiser/', 'legal.advertiser')->name('advertiser');
Route::view('/company/',    'legal.company')->name('company');
Route::view('/tokutei/',    'legal.tokutei')->name('tokutei');

// 代理店パートナー募集（noindex）
Route::view('/agency/', 'agency.index')->name('agency');

// お問い合わせ（一般）
Route::get('/inquiry/',  [\App\Http\Controllers\InquiryController::class, 'show'])->name('inquiry');
Route::post('/inquiry/', [\App\Http\Controllers\InquiryController::class, 'send'])->name('inquiry.send');

// 求人アラート登録（Web）
Route::get('/alert/',          [\App\Http\Controllers\AlertRegistrationController::class, 'show'])->name('alert.register');
Route::post('/alert/',         [\App\Http\Controllers\AlertRegistrationController::class, 'store'])->name('alert.store');
Route::get('/alert/{token}/',  [\App\Http\Controllers\AlertRegistrationController::class, 'complete'])->name('alert.complete');

// 店舗管理（要ログイン）
use App\Http\Controllers\Manage\ShopInfoController;
use App\Http\Controllers\Manage\BusinessController;
use App\Http\Controllers\Manage\CastJobController;
use App\Http\Controllers\Manage\StaffJobController;
use App\Http\Controllers\Manage\ContactController;
use App\Http\Controllers\Manage\ApplicationController as ManageApplicationController;

Route::middleware(['auth', 'verified'])->prefix('manage')->name('manage.')->group(function () {
    Route::get('/dashboard/',            [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/switch-shop/{shopId}/', [DashboardController::class, 'switchShop'])->name('switch-shop')->where('shopId', '[0-9]+');

    // 代理店ポータル
    Route::get('/partner/',                                [\App\Http\Controllers\Manage\PartnerPortalController::class, 'index'])->name('partner.index');
    Route::post('/partner/act-as/{shopId}/',               [\App\Http\Controllers\Manage\PartnerPortalController::class, 'actAs'])->name('partner.actAs')->where('shopId', '[0-9]+');
    Route::post('/partner/stop-acting/',                   [\App\Http\Controllers\Manage\PartnerPortalController::class, 'stopActing'])->name('partner.stopActing');
    Route::delete('/partner/shops/{shopId}/',              [\App\Http\Controllers\Manage\PartnerPortalController::class, 'destroyShop'])->name('partner.shops.destroy')->where('shopId', '[0-9]+');

    // 店舗基本情報
    Route::get('/shop/edit/',            [ShopInfoController::class, 'edit'])->name('shop.edit');
    Route::put('/shop/',                 [ShopInfoController::class, 'update'])->name('shop.update');

    // 画像
    Route::get('/shop/image/',           [ShopInfoController::class, 'editImage'])->name('shop.image');
    Route::post('/shop/image/',          [ShopInfoController::class, 'storeImage'])->name('shop.image.store');
    Route::delete('/shop/image/',        [ShopInfoController::class, 'destroyImage'])->name('shop.image.destroy');

    // 営業情報
    Route::get('/business/edit/',        [BusinessController::class, 'edit'])->name('business.edit');
    Route::put('/business/',             [BusinessController::class, 'update'])->name('business.update');

    // キャスト求人
    Route::get('/cast/',                 [CastJobController::class, 'index'])->name('cast.index');
    Route::get('/cast/create/',          [CastJobController::class, 'create'])->name('cast.create');
    Route::post('/cast/',                [CastJobController::class, 'store'])->name('cast.store');
    Route::get('/cast/{id}/edit/',       [CastJobController::class, 'edit'])->name('cast.edit')->where('id', '[0-9]+');
    Route::put('/cast/{id}/',            [CastJobController::class, 'update'])->name('cast.update')->where('id', '[0-9]+');
    Route::delete('/cast/{id}/',         [CastJobController::class, 'destroy'])->name('cast.destroy')->where('id', '[0-9]+');

    // 掲載申請
    Route::post('/apply/',               [\App\Http\Controllers\Manage\DashboardController::class, 'apply'])->name('apply');

    // 有料掲載タブ
    Route::get('/paid-plan/',            [\App\Http\Controllers\Manage\PaidPlanController::class, 'index'])->name('paid-plan');
    Route::post('/plan/apply/',          [\App\Http\Controllers\Manage\PaidPlanController::class, 'applyPlan'])->name('plan.apply');
    Route::patch('/bid-price/',          [\App\Http\Controllers\Manage\PaidPlanController::class, 'updateBidPrice'])->name('bid-price.update');

    // 応募管理・メッセージ
    Route::get('/applications/',                          [ManageApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{id}/',                     [ManageApplicationController::class, 'show'])->name('applications.show')->where('id', '[0-9]+');
    Route::post('/applications/{id}/message/',            [ManageApplicationController::class, 'message'])->name('applications.message')->where('id', '[0-9]+');
    Route::patch('/applications/{id}/status/',            [ManageApplicationController::class, 'updateStatus'])->name('applications.status')->where('id', '[0-9]+');

    // パスワード変更
    Route::get('/password/',             [\App\Http\Controllers\Manage\PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password/',             [\App\Http\Controllers\Manage\PasswordController::class, 'update'])->name('password.update');

    // お問い合わせ・要望
    Route::get('/contact/',              [ContactController::class, 'show'])->name('contact');
    Route::post('/contact/',             [ContactController::class, 'send'])->name('contact.send');

    // スタッフ求人
    Route::get('/staff/',                [StaffJobController::class, 'index'])->name('staff.index');
    Route::get('/staff/create/',         [StaffJobController::class, 'create'])->name('staff.create');
    Route::post('/staff/',               [StaffJobController::class, 'store'])->name('staff.store');
    Route::get('/staff/{id}/edit/',      [StaffJobController::class, 'edit'])->name('staff.edit')->where('id', '[0-9]+');
    Route::put('/staff/{id}/',           [StaffJobController::class, 'update'])->name('staff.update')->where('id', '[0-9]+');
    Route::delete('/staff/{id}/',        [StaffJobController::class, 'destroy'])->name('staff.destroy')->where('id', '[0-9]+');

    // LINE通知設定
    Route::delete('/line-notify/', [\App\Http\Controllers\Manage\LineNotifyController::class, 'remove'])->name('line-notify.remove');
});

// LINE Messaging API Webhook（CSRF除外 → bootstrap/app.phpで設定済み）
Route::post('/line/webhook/', [\App\Http\Controllers\LineWebhookController::class, 'handle'])->name('line.webhook');

// 検索（クエリ文字列ベース）
Route::get('/search/', [SearchController::class, 'index'])->name('search');

// 求人詳細
Route::get('/job/{id}/', [JobController::class, 'show'])->name('job.show')->where('id', '[0-9]+');

// 店舗詳細（営業リスト）
Route::get('/shop/{id}/', [App\Http\Controllers\ShopController::class, 'show'])->name('shop.show')->where('id', '[0-9]+');

// 通報フォーム
Route::post('/report/', [\App\Http\Controllers\ReportController::class, 'send'])->name('report.send');

// ホットリンククリック計測
Route::get('/click/{id}/', [JobController::class, 'click'])->name('click')->where('id', '[0-9]+');

// 応募フォーム
Route::get('/job/{jobId}/apply/', [ApplicationController::class, 'create'])->name('apply.create')->where('jobId', '[0-9]+');
Route::post('/job/{jobId}/apply/', [ApplicationController::class, 'store'])->name('apply.store')->where('jobId', '[0-9]+')->middleware('throttle:10,60');
Route::get('/job/{jobId}/apply/confirm/', [ApplicationController::class, 'confirm'])->name('apply.confirm')->where('jobId', '[0-9]+');
Route::post('/job/{jobId}/apply/confirm/', [ApplicationController::class, 'finalStore'])->name('apply.final-store')->where('jobId', '[0-9]+')->middleware('throttle:10,60');
Route::get('/job/{jobId}/apply/complete/', [ApplicationController::class, 'complete'])->name('apply.complete')->where('jobId', '[0-9]+');

// 応募者向けメッセージスレッド（ログイン不要）
Route::get('/apply/thread/{token}/', [\App\Http\Controllers\ThreadController::class, 'show'])->name('apply.thread');
Route::post('/apply/thread/{token}/message/', [\App\Http\Controllers\ThreadController::class, 'store'])->name('apply.thread.message')->middleware('throttle:20,60');

// 掲載継続確認（ログイン不要）
Route::get('/manage/alive/{token}/', [\App\Http\Controllers\Manage\AliveController::class, 'confirm'])->name('manage.alive');

// Admin（サイト管理者専用）
Route::middleware(['auth', 'admin', 'admin.ip'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard/',                      [AdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/keywords/',                       [AdminKeyword::class, 'index'])->name('keywords.index');
    Route::post('/keywords/{id}/map/',             [AdminKeyword::class, 'map'])->name('keywords.map')->where('id', '[0-9]+');
    Route::post('/keywords/{id}/confirm/',         [AdminKeyword::class, 'confirm'])->name('keywords.confirm')->where('id', '[0-9]+');
    Route::post('/keywords/{id}/exclude/',         [AdminKeyword::class, 'exclude'])->name('keywords.exclude')->where('id', '[0-9]+');
    Route::post('/keywords/{id}/reset/',           [AdminKeyword::class, 'reset'])->name('keywords.reset')->where('id', '[0-9]+');
    Route::post('/keywords/generate-candidates/', [AdminKeyword::class, 'generateCandidates'])->name('keywords.generate_candidates');
    Route::get('/master/',                                     [AdminMaster::class, 'index'])->name('master.index');
    Route::post('/master/area/',                               [AdminMaster::class, 'storeArea'])->name('master.area.store');
    Route::post('/master/job-type/',                           [AdminMaster::class, 'storeJobType'])->name('master.job_type.store');
    Route::patch('/master/job-type/{id}/',                     [AdminMaster::class, 'updateJobType'])->name('master.job_type.update')->where('id', '[0-9]+');
    Route::patch('/master/address-mapping/{id}/',              [AdminMaster::class, 'updateAddressMapping'])->name('master.address_mapping.update')->where('id', '[0-9]+');
    Route::delete('/master/address-mapping/{id}/',             [AdminMaster::class, 'deleteAddressMapping'])->name('master.address_mapping.delete')->where('id', '[0-9]+');

    // 店舗審査
    Route::get('/shops/',                          [\App\Http\Controllers\Admin\ShopReviewController::class, 'index'])->name('shops.index');
    Route::get('/shops/{id}/',                     [\App\Http\Controllers\Admin\ShopReviewController::class, 'show'])->name('shops.show')->where('id', '[0-9]+');
    Route::post('/shops/{id}/approve/',            [\App\Http\Controllers\Admin\ShopReviewController::class, 'approve'])->name('shops.approve')->where('id', '[0-9]+');
    Route::post('/shops/{id}/reject/',             [\App\Http\Controllers\Admin\ShopReviewController::class, 'reject'])->name('shops.reject')->where('id', '[0-9]+');
    Route::patch('/shops/{id}/bid-price/',         [\App\Http\Controllers\Admin\ShopReviewController::class, 'updateBidPrice'])->name('shops.updateBidPrice')->where('id', '[0-9]+');
    Route::post('/shops/{id}/add-budget/',         [\App\Http\Controllers\Admin\ShopReviewController::class, 'addBudget'])->name('shops.addBudget')->where('id', '[0-9]+');
    Route::patch('/shops/{id}/partner/',           [\App\Http\Controllers\Admin\ShopReviewController::class, 'updatePartner'])->name('shops.updatePartner')->where('id', '[0-9]+');
    Route::delete('/shops/{id}/',                  [\App\Http\Controllers\Admin\ShopReviewController::class, 'destroy'])->name('shops.destroy')->where('id', '[0-9]+');

    // パートナー管理
    Route::get('/partners/',                       [\App\Http\Controllers\Admin\PartnerController::class, 'index'])->name('partners.index');
    Route::get('/partners/create/',                [\App\Http\Controllers\Admin\PartnerController::class, 'create'])->name('partners.create');
    Route::post('/partners/',                      [\App\Http\Controllers\Admin\PartnerController::class, 'store'])->name('partners.store');
    Route::get('/partners/{partner}/',             [\App\Http\Controllers\Admin\PartnerController::class, 'show'])->name('partners.show');
    Route::get('/partners/{partner}/edit/',        [\App\Http\Controllers\Admin\PartnerController::class, 'edit'])->name('partners.edit');
    Route::put('/partners/{partner}/',             [\App\Http\Controllers\Admin\PartnerController::class, 'update'])->name('partners.update');
    Route::post('/partners/{partner}/commission/', [\App\Http\Controllers\Admin\PartnerController::class, 'addCommission'])->name('partners.addCommission');
    Route::post('/partners/{partner}/mark-paid/',  [\App\Http\Controllers\Admin\PartnerController::class, 'markPaid'])->name('partners.markPaid');
    Route::post('/partners/{partner}/user/',       [\App\Http\Controllers\Admin\PartnerController::class, 'createUser'])->name('partners.createUser');
    Route::get('/partners/{partner}/csv/',         [\App\Http\Controllers\Admin\PartnerController::class, 'downloadCsv'])->name('partners.downloadCsv');

    // 月次取引明細
    Route::get('/billing/',           [\App\Http\Controllers\Admin\BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/csv/',       [\App\Http\Controllers\Admin\BillingController::class, 'downloadCsv'])->name('billing.csv');
    Route::get('/billing/invoy-csv/', [\App\Http\Controllers\Admin\BillingController::class, 'downloadInvoy'])->name('billing.invoy-csv');

    // 有料プラン申し込み審査
    Route::get('/plan-applications/',                                          [\App\Http\Controllers\Admin\PlanApplicationController::class, 'index'])->name('plan-applications.index');
    Route::post('/plan-applications/{application}/approve/',                   [\App\Http\Controllers\Admin\PlanApplicationController::class, 'approve'])->name('plan-applications.approve');
    Route::post('/plan-applications/{application}/reject/',                    [\App\Http\Controllers\Admin\PlanApplicationController::class, 'reject'])->name('plan-applications.reject');

    // XML連携店舗一覧
    Route::get('/xml-shops/', [\App\Http\Controllers\Admin\XmlShopController::class, 'index'])->name('xml-shops.index');

    // XML外部連携先管理
    Route::get('/xml-feeds/',                    [\App\Http\Controllers\Admin\XmlFeedController::class, 'index'])->name('xml-feeds.index');
    Route::get('/xml-feeds/create/',             [\App\Http\Controllers\Admin\XmlFeedController::class, 'create'])->name('xml-feeds.create');
    Route::post('/xml-feeds/',                   [\App\Http\Controllers\Admin\XmlFeedController::class, 'store'])->name('xml-feeds.store');
    Route::get('/xml-feeds/{xmlFeed}/edit/',     [\App\Http\Controllers\Admin\XmlFeedController::class, 'edit'])->name('xml-feeds.edit');
    Route::put('/xml-feeds/{xmlFeed}/',          [\App\Http\Controllers\Admin\XmlFeedController::class, 'update'])->name('xml-feeds.update');
    Route::post('/xml-feeds/{xmlFeed}/toggle/',      [\App\Http\Controllers\Admin\XmlFeedController::class, 'toggleStatus'])->name('xml-feeds.toggle');
    Route::post('/xml-feeds/{xmlFeed}/add-budget/',  [\App\Http\Controllers\Admin\XmlFeedController::class, 'addBudget'])->name('xml-feeds.add-budget');

    // お知らせ配信
    Route::get('/notices/',                [\App\Http\Controllers\Admin\NoticeController::class, 'index'])->name('notices.index');
    Route::get('/notices/create/',         [\App\Http\Controllers\Admin\NoticeController::class, 'create'])->name('notices.create');
    Route::post('/notices/',               [\App\Http\Controllers\Admin\NoticeController::class, 'store'])->name('notices.store');
    Route::get('/notices/{notice}/',       [\App\Http\Controllers\Admin\NoticeController::class, 'show'])->name('notices.show');
    Route::post('/notices/{notice}/send/', [\App\Http\Controllers\Admin\NoticeController::class, 'send'])->name('notices.send');

    // コラム・ガイド記事管理
    Route::get('/articles/',                        [\App\Http\Controllers\Admin\ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/create/',                 [\App\Http\Controllers\Admin\ArticleController::class, 'create'])->name('articles.create');
    Route::post('/articles/',                       [\App\Http\Controllers\Admin\ArticleController::class, 'store'])->name('articles.store');
    Route::get('/articles/{article}/edit/',         [\App\Http\Controllers\Admin\ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/articles/{article}/',              [\App\Http\Controllers\Admin\ArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}/',           [\App\Http\Controllers\Admin\ArticleController::class, 'destroy'])->name('articles.destroy');
    Route::get('/articles/{article}/preview/',      [\App\Http\Controllers\Admin\ArticleController::class, 'preview'])->name('articles.preview');
    // 記事動画生成
    Route::post('/articles/{article}/video/',          [\App\Http\Controllers\Admin\ArticleVideoController::class, 'generate'])->name('articles.video.generate');
    Route::get('/articles/{article}/video/status/',    [\App\Http\Controllers\Admin\ArticleVideoController::class, 'status'])->name('articles.video.status');
    Route::get('/articles/{article}/video/download/',  [\App\Http\Controllers\Admin\ArticleVideoController::class, 'download'])->name('articles.video.download');
    Route::delete('/articles/{article}/video/',        [\App\Http\Controllers\Admin\ArticleVideoController::class, 'destroy'])->name('articles.video.destroy');
    // 検索PV分析
    Route::get('/search-page-views/', [\App\Http\Controllers\Admin\SearchPageViewController::class, 'index'])->name('search-page-views.index');

    // 記事テーマ管理
    Route::post('/article-topics/',                    [\App\Http\Controllers\Admin\ArticleTopicController::class, 'store'])->name('article-topics.store');
    Route::post('/article-topics/suggest/',            [\App\Http\Controllers\Admin\ArticleTopicController::class, 'suggest'])->name('article-topics.suggest');
    Route::patch('/article-topics/{topic}/approve/',   [\App\Http\Controllers\Admin\ArticleTopicController::class, 'approve'])->name('article-topics.approve');
    Route::delete('/article-topics/{topic}/',          [\App\Http\Controllers\Admin\ArticleTopicController::class, 'destroy'])->name('article-topics.destroy');

    // 記事生成プロンプト設定
    Route::put('/article-generation-prompts/{gender}/', [\App\Http\Controllers\Admin\ArticleController::class, 'updatePrompt'])->name('article-generation-prompts.update');
});

// コラム・ガイド記事（公開）
Route::get('/article/',         [\App\Http\Controllers\ArticleController::class, 'index'])->name('article.index');
Route::get('/article/{slug}/',  [\App\Http\Controllers\ArticleController::class, 'show'])->name('article.show')
    ->where('slug', '[a-z0-9_-]+');

// クリック計測（PPC課金）
Route::get('/track/job/{id}/',  [\App\Http\Controllers\TrackController::class, 'job'])->name('track.job')->where('id', '[0-9]+');
Route::get('/track/shop/{id}/', [\App\Http\Controllers\TrackController::class, 'shop'])->name('track.shop')->where('id', '[0-9]+');

// 検索（都道府県LP）
// 例: /male/tokyo/ or /female/osaka/
Route::get('/{gender}/{pref_slug}/', [SearchController::class, 'prefecture'])
    ->where([
        'gender'    => 'male|female|business',
        'pref_slug' => '[a-z0-9\-]+',
    ])
    ->name('search.prefecture');

// 検索（正規化ディレクトリURL）
// 例: /male/shinjuku/host/ or /female/ikebukuro/all/
Route::get('/{gender}/{area_slug}/{job_slug}/', [SearchController::class, 'directory'])
    ->where([
        'gender'    => 'male|female|business',
        'area_slug' => '[a-z0-9\-]+',
        'job_slug'  => '[a-z0-9\-]+',
    ])
    ->name('search.directory');

// 検索（フィルター付きディレクトリURL）
// 例: /male/shinjuku/all/hibarai/ or /female/all/all/mikeiken/
Route::get('/{gender}/{area_slug}/{job_slug}/{filter_slug}/', [SearchController::class, 'filteredDirectory'])
    ->where([
        'gender'      => 'male|female|business',
        'area_slug'   => '[a-z0-9\-]+',
        'job_slug'    => '[a-z0-9\-]+',
        'filter_slug' => '[a-z0-9\-]+',
    ])
    ->name('search.filtered_directory');
