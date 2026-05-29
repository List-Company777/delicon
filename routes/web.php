<?php

use App\Http\Controllers\TopController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Manage\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\MasterController as AdminMaster;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\CastController;
use App\Http\Controllers\Manage\ShopInfoController;
use App\Http\Controllers\Manage\ShopNewsController;
use App\Http\Controllers\Manage\BusinessController;
use App\Http\Controllers\Manage\ContactController;
use App\Http\Controllers\Manage\ApplicationController as ManageApplicationController;
use App\Http\Controllers\Auth\VisitorRegisterController;
use App\Http\Controllers\Manage\CastProfileController;
use App\Http\Controllers\Manage\CastAnalyticsController;
use App\Http\Controllers\Manage\ReviewManageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CastReviewController;

use Illuminate\Support\Facades\Route;

// official_hp 経営コックピット向け売上エクスポートAPI（token認証）
Route::get('/api/sales/export/', [\App\Http\Controllers\Api\SalesExportController::class, 'export'])->name('api.sales.export');

// サイトマップ（静的ファイルより先にルートで処理）
Route::get('/sitemap.xml',        [SitemapController::class, 'main']);
Route::get('/sitemap-detail.xml', [SitemapController::class, 'detail']);
Route::get('/sitemap-pages.xml',  [SitemapController::class, 'pages']);

// トップページ
Route::get('/', [TopController::class, 'index'])->name('top');

// 認証（ゲストのみ）
Route::middleware('guest')->group(function () {
    Route::get('/login/',    [LoginController::class, 'show'])->name('login');
    Route::post('/login/',   [LoginController::class, 'store'])->middleware('throttle:login');
    Route::get('/register/', [RegisterController::class, 'show'])->name('register');
    Route::post('/register/', [RegisterController::class, 'store'])->middleware('throttle:5,30');
    // www.up-stage.info連携店舗の引き継ぎ検索（登録フォームのAJAX）
    Route::get('/api/xml-shops/search/', [RegisterController::class, 'xmlShopSearch'])->name('api.xml-shops.search');
    // パスワードリセット
    Route::get('/forgot-password/',        [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password/',       [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'send'])->middleware('throttle:5,60')->name('password.email');
    Route::get('/reset-password/{token}/', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password/',        [\App\Http\Controllers\Auth\ResetPasswordController::class, 'update'])->middleware('throttle:5,60')->name('password.store');
});

// ログアウト
Route::post('/logout/', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// 訪問者登録
Route::middleware('guest')->group(function () {
    Route::get('/visitor-register/',  [VisitorRegisterController::class, 'show'])->name('visitor.register');
    Route::post('/visitor-register/', [VisitorRegisterController::class, 'store'])->middleware('throttle:5,30')->name('visitor.register.store');
});

// 口コミ投稿（ログイン必須）
Route::middleware('auth')->group(function () {
    Route::get('/reviews/create/',  [ReviewController::class, 'create'])->name('review.create');
    Route::post('/reviews/',        [ReviewController::class, 'store'])->name('review.store')->middleware('throttle:10,60');
    Route::post('/casts/{castId}/reviews/', [CastReviewController::class, 'store'])->name('cast.review.store')->middleware('throttle:5,60')->where('castId', '[0-9]+');
});

// メール認証
Route::middleware('auth')->group(function () {
    Route::get('/email/verify/', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::post('/email/verification-notification/', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('resent', true);
    })->middleware('throttle:6,1')->name('verification.send');
});

// 認証リンクは未ログイン状態のデバイスからもクリックできるよう auth 不要
Route::get('/email/verify/{id}/{hash}/', function (\Illuminate\Http\Request $request, $id, $hash) {
    $user = \App\Models\User::findOrFail($id);

    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403);
    }

    $alreadyVerified = $user->hasVerifiedEmail();

    if (!$alreadyVerified) {
        $user->markEmailAsVerified();
    }

    if (!auth()->check()) {
        \Illuminate\Support\Facades\Auth::login($user);
    }

    return redirect()->route('manage.dashboard')
        ->with('verified', !$alreadyVerified);
})->middleware('signed')->name('verification.verify');

// 無料掲載LP
Route::view('/keisai/', 'lp.keisai')->name('lp.keisai');
Route::view('/link/', 'lp.link')->name('lp.link');

// 夜ビズ LP
Route::view('/yorubiz/', 'lp.yorubiz')->name('lp.yorubiz');
Route::view('/features/', 'features')->name('features');
Route::view('/bundle/', 'bundle')->name('bundle');
Route::post('/diary/{diary}/like/', [\App\Http\Controllers\DiaryLikeController::class, 'toggle'])->name('diary.like.toggle')->middleware('auth')->where('diary', '[0-9]+');
Route::get('/ranking/', [\App\Http\Controllers\RankingController::class, 'index'])->name('ranking.index');
Route::post('/ranking/tel/{castId}/', [\App\Http\Controllers\RankingController::class, 'recordTelClick'])->name('ranking.tel-click')->where('castId', '[0-9]+');

// 法的ページ・会社情報
Route::view('/privacy/',    'legal.privacy')->name('privacy');
Route::view('/terms/',      'legal.terms')->name('terms');
Route::view('/advertiser/', 'legal.advertiser')->name('advertiser');
Route::view('/company/',    'legal.company')->name('company');
Route::view('/welcome/',    'welcome_migration')->name('welcome.migration');
Route::view('/delicon/',    'welcome_migration')->name('delicon.guide');
// 代理店パートナー募集（noindex）
Route::view('/agency/', 'agency.index')->name('agency');

// 検索サジェスト API
Route::get('/suggest', [\App\Http\Controllers\SuggestController::class, 'search'])->name('suggest')->middleware('throttle:60,1');

// お問い合わせ（一般）
Route::get('/inquiry/',  [\App\Http\Controllers\InquiryController::class, 'show'])->name('inquiry');
Route::post('/inquiry/', [\App\Http\Controllers\InquiryController::class, 'send'])->name('inquiry.send')->middleware('throttle:5,10');

// 求人アラート登録（Web）
Route::get('/alert/',          [\App\Http\Controllers\AlertRegistrationController::class, 'show'])->name('alert.register');
Route::post('/alert/',         [\App\Http\Controllers\AlertRegistrationController::class, 'store'])->name('alert.store')->middleware('throttle:5,10');
Route::get('/alert/{token}/',  [\App\Http\Controllers\AlertRegistrationController::class, 'complete'])->name('alert.complete');

// 店舗管理（要ログイン）

Route::middleware(['auth', 'verified'])->prefix('manage')->name('manage.')->group(function () {
    Route::get('/dashboard/',            [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/switch-shop/{shopId}/', [DashboardController::class, 'switchShop'])->name('switch-shop')->where('shopId', '[0-9]+');

    // 代理店ポータル
    Route::get('/partner/',                                [\App\Http\Controllers\Manage\PartnerPortalController::class, 'index'])->name('partner.index');
    Route::post('/partner/act-as/{shopId}/',               [\App\Http\Controllers\Manage\PartnerPortalController::class, 'actAs'])->name('partner.actAs')->where('shopId', '[0-9]+');
    Route::post('/partner/stop-acting/',                   [\App\Http\Controllers\Manage\PartnerPortalController::class, 'stopActing'])->name('partner.stopActing');
    Route::delete('/partner/shops/{shopId}/',              [\App\Http\Controllers\Manage\PartnerPortalController::class, 'destroyShop'])->name('partner.shops.destroy')->where('shopId', '[0-9]+');
    Route::post('/partner/shops/{shopId}/plan-apply/',     [\App\Http\Controllers\Manage\PartnerPortalController::class, 'applyPlan'])->name('partner.shops.planApply')->where('shopId', '[0-9]+');

    // 店舗基本情報
    Route::get('/shop/edit/',            [ShopInfoController::class, 'edit'])->name('shop.edit');
    Route::put('/shop/',                 [ShopInfoController::class, 'update'])->name('shop.update');

    // 画像
    Route::get('/shop/image/',           [ShopInfoController::class, 'editImage'])->name('shop.image');
    Route::post('/shop/image/',          [ShopInfoController::class, 'storeImage'])->name('shop.image.store');
    Route::delete('/shop/image/',        [ShopInfoController::class, 'destroyImage'])->name('shop.image.destroy');

    // お知らせ管理
    Route::get('/shop/news/',            [ShopNewsController::class, 'index'])->name('shop.news.index');
    Route::post('/shop/news/',           [ShopNewsController::class, 'store'])->name('shop.news.store');
    Route::patch('/shop/news/{news}/',   [ShopNewsController::class, 'togglePin'])->name('shop.news.pin');
    Route::delete('/shop/news/{news}/',  [ShopNewsController::class, 'destroy'])->name('shop.news.destroy');

    // 営業情報
    Route::get('/business/edit/',        [BusinessController::class, 'edit'])->name('business.edit');
    Route::put('/business/',             [BusinessController::class, 'update'])->name('business.update');


    // 在籍キャスト管理（deliconサイト専用）
    Route::get('/casts/',                 [CastProfileController::class, 'index'])->name('cast-profile.index');
    Route::get('/casts/create/',          [CastProfileController::class, 'create'])->name('cast-profile.create');
    Route::post('/casts/',                [CastProfileController::class, 'store'])->name('cast-profile.store');
    Route::post("/casts/reorder/",          [CastProfileController::class, "reorder"])->name("cast-profile.reorder");
    Route::get('/casts/{id}/edit/',       [CastProfileController::class, 'edit'])->name('cast-profile.edit')->where('id', '[0-9]+');
    Route::get('/casts/analytics/',       [CastAnalyticsController::class, 'index'])->name('cast-analytics.index');
    Route::get('/diaries/',                       [\App\Http\Controllers\Manage\CastDiaryController::class, 'shopDiaries'])->name('diaries.index');
    Route::get('/shift-requests/',                [\App\Http\Controllers\Manage\ShiftRequestController::class, 'index'])->name('shift-requests.index');
    Route::patch('/shift-requests/{id}/approve/', [\App\Http\Controllers\Manage\ShiftRequestController::class, 'approve'])->name('shift-requests.approve')->where('id', '[0-9]+');
    Route::patch('/shift-requests/{id}/reject/',  [\App\Http\Controllers\Manage\ShiftRequestController::class, 'reject'])->name('shift-requests.reject')->where('id', '[0-9]+');
        Route::get('/casts/{castId}/diaries/',        [\App\Http\Controllers\Manage\CastDiaryController::class, 'index'])->name('cast-diary.index')->where('castId', '[0-9]+');
    Route::get('/casts/{castId}/diaries/create/', [\App\Http\Controllers\Manage\CastDiaryController::class, 'create'])->name('cast-diary.create')->where('castId', '[0-9]+');
    Route::post('/casts/{castId}/diaries/',       [\App\Http\Controllers\Manage\CastDiaryController::class, 'store'])->name('cast-diary.store')->where('castId', '[0-9]+');
    Route::delete('/diaries/{diary}/',            [\App\Http\Controllers\Manage\CastDiaryController::class, 'destroy'])->name('cast-diary.destroy');
    Route::post('/casts/{castId}/diary-token/',       [\App\Http\Controllers\Manage\CastDiaryController::class, 'issueToken'])->name('cast-diary.issue-token')->where('castId', '[0-9]+');
    Route::post('/casts/{castId}/diary-email-token/', [\App\Http\Controllers\Manage\CastDiaryController::class, 'issueEmailToken'])->name('cast-diary.issue-email-token')->where('castId', '[0-9]+');
    Route::put('/casts/{id}/',            [CastProfileController::class, 'update'])->name('cast-profile.update')->where('id', '[0-9]+');
    Route::delete('/casts/{id}/',         [CastProfileController::class, 'destroy'])->name('cast-profile.destroy')->where('id', '[0-9]+');
    Route::get('/casts/{castId}/schedules/',             [\App\Http\Controllers\Manage\CastScheduleController::class, 'index'])->name('cast-schedule.index')->where('castId', '[0-9]+');
    Route::post('/casts/{castId}/schedules/',            [\App\Http\Controllers\Manage\CastScheduleController::class, 'store'])->name('cast-schedule.store')->where('castId', '[0-9]+');
    Route::delete('/casts/{castId}/schedules/{scheduleId}/', [\App\Http\Controllers\Manage\CastScheduleController::class, 'destroy'])->name('cast-schedule.destroy')->where(['castId' => '[0-9]+', 'scheduleId' => '[0-9]+']);

    // 口コミ管理
    Route::get('/reviews/',                            [ReviewManageController::class, 'index'])->name('review.index');
    Route::get('/reviews/users/{userId}/',             [ReviewManageController::class, 'showUser'])->name('review.user')->where('userId','[0-9]+');
    Route::patch('/reviews/{reviewId}/status/',        [ReviewManageController::class, 'updateStatus'])->name('review.status')->where('reviewId','[0-9]+');
    Route::post('/reviews/{reviewId}/delete-request/',  [ReviewManageController::class, 'requestDeletion'])->name('review.delete-request')->where('reviewId','[0-9]+');
    Route::post('/reviews/{reviewId}/reply/',          [ReviewManageController::class, 'reply'])->name('review.reply')->where('reviewId','[0-9]+');
    Route::delete('/reviews/{reviewId}/reply/',       [ReviewManageController::class, 'deleteReply'])->name('review.reply.delete')->where('reviewId','[0-9]+');
    Route::post('/reviews/{reviewId}/coupon/',         [ReviewManageController::class, 'sendCoupon'])->name('review.coupon.send')->where('reviewId','[0-9]+');
    
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
    Route::patch('/email/',              [\App\Http\Controllers\Manage\PasswordController::class, 'updateEmail'])->name('email.update');


    // キャスト求人管理
    Route::get('/cast-jobs/',                  [\App\Http\Controllers\Manage\CastJobController::class, 'index'])->name('cast.index');
    Route::get('/cast-jobs/create/',            [\App\Http\Controllers\Manage\CastJobController::class, 'create'])->name('cast.create');
    Route::post('/cast-jobs/',                  [\App\Http\Controllers\Manage\CastJobController::class, 'store'])->name('cast.store');
    Route::get('/cast-jobs/{id}/edit/',         [\App\Http\Controllers\Manage\CastJobController::class, 'edit'])->name('cast.edit')->where('id', '[0-9]+');
    Route::put('/cast-jobs/{id}/',              [\App\Http\Controllers\Manage\CastJobController::class, 'update'])->name('cast.update')->where('id', '[0-9]+');
    Route::delete('/cast-jobs/{id}/',           [\App\Http\Controllers\Manage\CastJobController::class, 'destroy'])->name('cast.destroy')->where('id', '[0-9]+');

    // スタッフ求人管理
    Route::get('/staff-jobs/',                  [\App\Http\Controllers\Manage\StaffJobController::class, 'index'])->name('staff.index');
    Route::get('/staff-jobs/create/',            [\App\Http\Controllers\Manage\StaffJobController::class, 'create'])->name('staff.create');
    Route::post('/staff-jobs/',                  [\App\Http\Controllers\Manage\StaffJobController::class, 'store'])->name('staff.store');
    Route::get('/staff-jobs/{id}/edit/',         [\App\Http\Controllers\Manage\StaffJobController::class, 'edit'])->name('staff.edit')->where('id', '[0-9]+');
    Route::put('/staff-jobs/{id}/',              [\App\Http\Controllers\Manage\StaffJobController::class, 'update'])->name('staff.update')->where('id', '[0-9]+');
    Route::delete('/staff-jobs/{id}/',           [\App\Http\Controllers\Manage\StaffJobController::class, 'destroy'])->name('staff.destroy')->where('id', '[0-9]+');

    // お問い合わせ・要望
    Route::get('/contact/',              [ContactController::class, 'show'])->name('contact');
    Route::post('/contact/',             [ContactController::class, 'send'])->name('contact.send');

});

// 検索（クエリ文字列ベース）
Route::get('/search/', [SearchController::class, 'index'])->name('search');

// 求人詳細
Route::get('/job/{id}/', [JobController::class, 'show'])->name('job.show')->where('id', '[0-9]+');
// 通報フォーム
Route::post('/report/', [\App\Http\Controllers\ReportController::class, 'send'])->name('report.send')->middleware('throttle:10,10');

// ホットリンククリック計測

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

// なりすまし解除（auth のみ・admin不要）
Route::post('/admin/shops/stop-impersonating/', [\App\Http\Controllers\Admin\ShopReviewController::class, 'stopImpersonating'])->name('admin.shops.stopImpersonating')->middleware('auth');

// Admin（サイト管理者専用）
Route::middleware(['auth', 'admin', 'admin.ip'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard/',                      [AdminDashboard::class, 'index'])->name('dashboard');
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
    Route::patch('/shops/{id}/area/',              [\App\Http\Controllers\Admin\ShopReviewController::class, 'updateArea'])->name('shops.updateArea')->where('id', '[0-9]+');
    Route::patch('/shops/{id}/plan/',              [\App\Http\Controllers\Admin\ShopReviewController::class, 'updatePlan'])->name('shops.updatePlan')->where('id', '[0-9]+');
    Route::patch('/shops/{id}/genre/',             [\App\Http\Controllers\Admin\ShopReviewController::class, 'updateGenre'])->name('shops.updateGenre')->where('id', '[0-9]+');
    Route::patch('/shops/{id}/shop-type/',         [\App\Http\Controllers\Admin\ShopReviewController::class, 'updateShopType'])->name('shops.updateShopType')->where('id', '[0-9]+');
    Route::post('/shops/{id}/urls/',              [\App\Http\Controllers\Admin\ShopReviewController::class, 'updateExternalUrls'])->name('shops.updateUrls')->where('id', '[0-9]+');
    Route::get('/banner-check/',                   [\App\Http\Controllers\Admin\BannerCheckController::class, 'index'])->name('banner-check.index');
    Route::patch('/banner-check/{shop}/apply/',     [\App\Http\Controllers\Admin\BannerCheckController::class, 'applyBanner'])->name('banner-check.apply');
    Route::patch('/banner-check/{shop}/manual-ok/', [\App\Http\Controllers\Admin\BannerCheckController::class, 'manualOk'])->name('banner-check.manual-ok');
    Route::post('/banner-check/{id}/check/',       [\App\Http\Controllers\Admin\BannerCheckController::class, 'check'])->name('banner-check.check')->where('id', '[0-9]+');
    Route::delete('/shops/{id}/',                  [\App\Http\Controllers\Admin\ShopReviewController::class, 'destroy'])->name('shops.destroy')->where('id', '[0-9]+');
    Route::post('/shops/{id}/login-as/',          [\App\Http\Controllers\Admin\ShopReviewController::class, 'loginAs'])->name('shops.loginAs')->where('id', '[0-9]+');
    Route::get('/shops/{id}/permit-download/',     [\App\Http\Controllers\Admin\ShopReviewController::class, 'downloadPermit'])->name('shops.permit-download')->where('id', '[0-9]+');
    Route::post('/shops/{id}/permit-set/',         [\App\Http\Controllers\Admin\ShopReviewController::class, 'setPermit'])->name('shops.permit-set')->where('id', '[0-9]+');

    // エリア名不一致レビュー
    Route::get('/area-mismatch/', [\App\Http\Controllers\Admin\AreaMismatchController::class, 'index'])->name('area-mismatch.index');
    Route::patch('/area-mismatch/{shop}/apply/', [\App\Http\Controllers\Admin\AreaMismatchController::class, 'apply'])->name('area-mismatch.apply');
    Route::patch('/area-mismatch/{shop}/apply-pref/', [\App\Http\Controllers\Admin\AreaMismatchController::class, 'applyPref'])->name('area-mismatch.apply-pref');
    Route::patch('/area-mismatch/{shop}/dismiss/', [\App\Http\Controllers\Admin\AreaMismatchController::class, 'dismiss'])->name('area-mismatch.dismiss');


    // URL死活チェック
    Route::get('/url-check/', [\App\Http\Controllers\Admin\UrlCheckController::class, 'index'])->name('url-check.index');
    Route::patch('/url-check/{shopExternalUrl}/dismiss/', [\App\Http\Controllers\Admin\UrlCheckController::class, 'dismiss'])->name('url-check.dismiss');
    Route::patch('/url-check/{shopExternalUrl}/deactivate/', [\App\Http\Controllers\Admin\UrlCheckController::class, 'deactivate'])->name('url-check.deactivate');

    // 代理店移管
    Route::get('/partner-transfer/', [\App\Http\Controllers\Admin\PartnerTransferController::class, 'index'])->name('partner-transfer.index');

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
    Route::get('/notices/{notice}/edit/',  [\App\Http\Controllers\Admin\NoticeController::class, 'edit'])->name('notices.edit');
    Route::put('/notices/{notice}/',       [\App\Http\Controllers\Admin\NoticeController::class, 'update'])->name('notices.update');

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
    Route::get('/tel-click-analytics/', [\App\Http\Controllers\Admin\TelClickAnalyticsController::class, 'index'])->name('tel-click-analytics.index');

    // 記事テーマ管理
    Route::post('/article-topics/',                    [\App\Http\Controllers\Admin\ArticleTopicController::class, 'store'])->name('article-topics.store');
    Route::post('/article-topics/suggest/',            [\App\Http\Controllers\Admin\ArticleTopicController::class, 'suggest'])->name('article-topics.suggest')->middleware('throttle:10,1');
    Route::patch('/article-topics/{topic}/approve/',   [\App\Http\Controllers\Admin\ArticleTopicController::class, 'approve'])->name('article-topics.approve');
    Route::delete('/article-topics/{topic}/',          [\App\Http\Controllers\Admin\ArticleTopicController::class, 'destroy'])->name('article-topics.destroy');

    // 記事生成プロンプト設定
    Route::put('/article-generation-prompts/{gender}/', [\App\Http\Controllers\Admin\ArticleController::class, 'updatePrompt'])->name('article-generation-prompts.update');

    // 写メ日記・口コミ・削除申請（管理者用）
    Route::get('/cast-diaries/', [\App\Http\Controllers\Admin\CastDiaryController::class, 'index'])->name('cast-diaries.index');
    Route::delete('/cast-diaries/{diary}/', [\App\Http\Controllers\Admin\CastDiaryController::class, 'destroy'])->name('cast-diaries.destroy');
    Route::patch('/cast-diaries/{diary}/approve/', [\App\Http\Controllers\Admin\CastDiaryController::class, 'approve'])->name('cast-diaries.approve');
    Route::get('/cast-reviews/', [\App\Http\Controllers\Admin\CastReviewController::class, 'index'])->name('cast-reviews.index');
    Route::patch('/cast-reviews/{review}/approve/', [\App\Http\Controllers\Admin\CastReviewController::class, 'approve'])->name('cast-reviews.approve');
    Route::delete('/cast-reviews/{review}/reject/', [\App\Http\Controllers\Admin\CastReviewController::class, 'reject'])->name('cast-reviews.reject');
    Route::get('/deletion-requests/', [\App\Http\Controllers\Admin\CastDeletionRequestController::class, 'index'])->name('deletion-requests.index');
    Route::patch('/deletion-requests/{deletionRequest}/', [\App\Http\Controllers\Admin\CastDeletionRequestController::class, 'process'])->name('deletion-requests.process');
});

// ========== delicon 公開ルート ==========

// 店舗一覧・詳細
Route::get('/top/detail/{slug}/', fn() => redirect('/all/shop-list/', 301))->where('slug', '[a-z][a-z0-9-]+');
Route::get('/shops/', fn() => redirect('/all/shop-list/', 301))->name('shop.index');
Route::get('/shops/{shop}/', [ShopController::class, 'show'])->name('shop.show')->where('shop', '[0-9]+');
Route::get('/shops/{pref}/', fn(string $pref) => redirect("/{$pref}/shop-list/", 301))->name('shop.pref')->where('pref', '[a-z][a-z0-9-]+');
Route::get('/shops/{pref}/{area}/', fn(string $pref, string $area) => redirect("/{$area}/shop-list/", 301))->name('shop.pref_area')->where('pref', '[a-z][a-z0-9-]+')->where('area', '[a-z][a-z0-9-]+');

// キャスト一覧・詳細
// ユーザーダッシュボード・設定

Route::middleware('auth')->group(function () {
    Route::get('/user/dashboard/', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::get('/user/settings/', [UserDashboardController::class, 'settings'])->name('user.settings');
    Route::get('/user/coupons/', [UserDashboardController::class, 'coupons'])->name('user.coupons');
    Route::post('/user/settings/', [UserDashboardController::class, 'updateSettings'])->name('user.settings.update');
    Route::post('/user/notify-working/', [UserDashboardController::class, 'toggleNotifyWorking'])->name('user.notify-working.toggle');
    Route::post('/favorites/{cast}/', [FavoriteController::class, 'toggle'])->name('favorite.toggle');
    Route::post('/shops/{shop}/notify/', [\App\Http\Controllers\ShopNotificationController::class, 'toggle'])->name('shop.notify.toggle');
});

Route::get('/cast/', fn() => redirect('/all/girl-list/', 301))->name('cast.index');
Route::get('/cast/{cast}/', [CastController::class, 'show'])->name('cast.show')->where('cast', '[0-9]+');

// キャスト写メ日記投稿（トークンURL）
Route::get('/diary/post/{token}/',  [\App\Http\Controllers\DiaryPostController::class, 'show'])->name('diary.post.show');
Route::post('/diary/post/{token}/', [\App\Http\Controllers\DiaryPostController::class, 'store'])->name('diary.post.store')->middleware('throttle:5,1');
Route::post('/diary/post/{token}/shift-request/', [\App\Http\Controllers\DiaryPostController::class, 'storeShiftRequest'])->name('diary.shift-request.store');
Route::delete('/diary/post/{token}/shift-request/{id}/', [\App\Http\Controllers\DiaryPostController::class, 'destroyShiftRequest'])->name('diary.shift-request.destroy');
Route::post('/cast/{cast}/deletion-request/', [CastController::class, 'submitDeletionRequest'])->name('cast.deletion-request')->where('cast', '[0-9]+')->middleware('throttle:3,10');

// コラム・ガイド記事（公開）
Route::get('/article/',         [\App\Http\Controllers\ArticleController::class, 'index'])->name('article.index');
Route::get('/article/{slug}/',  [\App\Http\Controllers\ArticleController::class, 'show'])->name('article.show')
    ->where('slug', '[a-z0-9_-]+');

// クリック計測（PPC課金）
Route::get('/track/job/{id}/',  [\App\Http\Controllers\TrackController::class, 'job'])->name('track.job')->where('id', '[0-9]+');
Route::get('/track/shop/{id}/', [\App\Http\Controllers\TrackController::class, 'shop'])->name('track.shop')->where('id', '[0-9]+');

// /business/* → /yoasobi/* 301リダイレクト（旧URLの念のための保護）
Route::get('/business/{any}', fn(string $any) => redirect('/yoasobi/' . $any, 301))
    ->where('any', '.*');
Route::get('/{area_slug}/ranking/', [\App\Http\Controllers\RankingController::class, 'bySlug'])
    ->where(['area_slug' => '[a-z0-9\-]+'])
    ->name('ranking.area');
// 店舗一覧: /{area}/shop-list/  and  /{area}/shop-list/{filter}/
Route::get('/{area_slug}/shop-list/', [\App\Http\Controllers\SearchController::class, 'shopList'])
    ->where(['area_slug' => '[a-z0-9\-]+'])
    ->name('shop.list');

Route::get('/{area_slug}/shop-list/{filter_slug}/', [\App\Http\Controllers\SearchController::class, 'shopListFilter'])
    ->where(['area_slug' => '[a-z0-9\-]+', 'filter_slug' => '[a-z0-9\-]+'])
    ->name('shop.list.filter');

// Web Push 購読管理
Route::post('/push/subscribe/',   [\App\Http\Controllers\PushController::class, 'subscribe'])->name('push.subscribe')->middleware('throttle:10,1');
Route::post('/push/unsubscribe/', [\App\Http\Controllers\PushController::class, 'unsubscribe'])->name('push.unsubscribe');

// 旧URLからの301リダイレクト (SEO保全)
Route::get('/{gender}/{area_slug}/', fn($gender, $area_slug) => redirect("/{$area_slug}/shop-list/", 301))
    ->where(['gender' => 'female|male|yoasobi', 'area_slug' => '[a-z0-9\-]+']);

Route::get('/{gender}/{area_slug}/{job_slug}/', function ($gender, $area_slug, $job_slug) {
    $filter = ($job_slug === 'all') ? '' : "/{$job_slug}";
    return redirect("/{$area_slug}/shop-list{$filter}/", 301);
})->where(['gender' => 'female|male|yoasobi', 'area_slug' => '[a-z0-9\-]+', 'job_slug' => '[a-z0-9\-]+']);

Route::get('/{gender}/{area_slug}/{job_slug}/{filter_slug}/', function ($gender, $area_slug, $job_slug, $filter_slug) {
    $jobPart = ($job_slug === 'all') ? '' : "/{$job_slug}";
    return redirect("/{$area_slug}/shop-list{$jobPart}/{$filter_slug}/", 301);
})->where(['gender' => 'female|male|yoasobi', 'area_slug' => '[a-z0-9\-]+', 'job_slug' => '[a-z0-9\-]+', 'filter_slug' => '[a-z0-9\-]+']);

// 女性一覧 (girl-list) タブ
Route::get('/{area_slug}/girl-list/', [\App\Http\Controllers\GirlListController::class, 'index'])
    ->where(['area_slug' => '[a-z0-9\-]+'])
    ->name('girl.list');

Route::get('/{area_slug}/girl-list/{cast_tab}/', [\App\Http\Controllers\GirlListController::class, 'tab'])
    ->where(['area_slug' => '[a-z0-9\-]+', 'cast_tab' => 'standby|new|diary|review'])
    ->name('girl.list.tab');

Route::get('/{area_slug}/girl-list/type/{type_slug}/', [\App\Http\Controllers\GirlListController::class, 'byType'])
    ->where(['area_slug' => '[a-z0-9\-]+', 'type_slug' => '[a-z0-9\-]+'])
    ->name('girl.list.type');
Route::get('/{area_slug}/girl-list/age/{age_slug}/', [\App\Http\Controllers\GirlListController::class, 'byAge'])
    ->where(['area_slug' => '[a-z0-9\-]+', 'age_slug' => '[a-z0-9\-]+'])
    ->name('girl.list.age');
// エリアトップ
Route::get('/{area_slug}/', [\App\Http\Controllers\AreaTopController::class, 'show'])
    ->where(['area_slug' => '[a-z0-9\-]+'])
    ->name('area.top');

Route::post('/webhook/resend/', [\App\Http\Controllers\ResendWebhookController::class, 'handle'])->name('webhook.resend');
