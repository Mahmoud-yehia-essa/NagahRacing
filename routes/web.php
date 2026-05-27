<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdsController;
use App\Http\Controllers\AppVersionController;
use App\Http\Controllers\CamalController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FestivalController;
use App\Http\Controllers\FestivalPointController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GroubController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\LiveBroadcastController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NominationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationDashboardController;
use App\Http\Controllers\NotificationForAppController;
use App\Http\Controllers\PayMentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\PrizeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionAIController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\SponsorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CamelWorkerController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\UserSubscriptionController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/admin', function () {
    // return view('welcome');
    return redirect()->route('dashboard');
});



Route::get('/clear-cache', function() {
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    
    // Run migrations automatically to apply the new index
    try {
        Artisan::call('migrate', ['--force' => true]);
    } catch (\Exception $e) {
        \Log::error("Migration failed: " . $e->getMessage());
    }
    
    return "تم مسح كاش الإعدادات، والمسارات، والواجهات، والذاكرة المؤقتة وتشغيل الهجرات بنجاح على الاستضافة!";
});


Route::get('/debug-speed', function() {
    $t0 = microtime(true);
    
    // 1. Database Connection Speed
    $db_time = 'Error';
    try {
        $db_start = microtime(true);
        \DB::connection()->getPdo();
        $db_time = number_format(microtime(true) - $db_start, 4) . ' seconds';
    } catch (\Exception $e) {
        $db_time = 'Failed: ' . $e->getMessage();
    }
    
    // 2. OPCache Status
    $opcache_loaded = extension_loaded('opcache') ? 'Yes' : 'No';
    $opcache_enabled = function_exists('opcache_get_status') && !empty(opcache_get_status()['opcache_enabled']) ? 'Enabled' : 'Disabled';
    
    // 3. Check for Cached Configurations (potential local path leak)
    $config_cached = file_exists(base_path('bootstrap/cache/config.php')) ? 'Yes (Can cause path issues)' : 'No (Clean)';
    $route_cached = file_exists(base_path('bootstrap/cache/routes.php')) ? 'Yes (Can cause path issues)' : 'No (Clean)';
    
    $total_time = number_format(microtime(true) - $t0, 4) . ' seconds';
    
    return response()->json([
        'PHP Version' => PHP_VERSION,
        'Database Connection Time' => $db_time,
        'OPCache Extension Loaded' => $opcache_loaded,
        'OPCache Status' => $opcache_enabled,
        'Config Cached' => $config_cached,
        'Route Cached' => $route_cached,
        'Session Driver' => config('session.driver'),
        'Cache Store' => config('cache.default'),
        'Queue Connection' => config('queue.default'),
        'App Debug Mode' => config('app.debug') ? 'ON (Slow)' : 'OFF (Fast)',
        'Diagnostics Exec Time' => $total_time
    ]);
});


Route::get('/debug-queries-log', function() {
    $log_path = storage_path('logs/laravel.log');
    if (!file_exists($log_path)) {
        return 'Log file does not exist.';
    }
    
    $file = file($log_path);
    $lines = array_slice($file, -150); // get last 150 lines
    
    header('Content-Type: text/plain; charset=utf-8');
    echo implode("", $lines);
    exit;
});


Route::get('/debug-db-status', function() {
    $results = [];
    
    // 1. Check migrations table
    try {
        $migrations = \DB::table('migrations')->get();
        $results['migrations_in_db'] = $migrations->pluck('migration')->toArray();
    } catch (\Exception $e) {
        $results['migrations_error'] = $e->getMessage();
    }
    
    // 2. Check indexes of notifications table
    try {
        $indexes = \DB::select('SHOW INDEX FROM notifications');
        $results['notifications_indexes'] = collect($indexes)->pluck('Key_name')->unique()->toArray();
    } catch (\Exception $e) {
        $results['notifications_indexes_error'] = $e->getMessage();
    }
    
    // 3. Count in notifications table
    try {
        $t0 = microtime(true);
        $total = \DB::table('notifications')->count();
        $t1 = microtime(true);
        $results['notifications_total_count'] = $total;
        $results['notifications_count_query_time'] = number_format(($t1 - $t0) * 1000, 2) . ' ms';
    } catch (\Exception $e) {
        $results['notifications_count_error'] = $e->getMessage();
    }
    
    // 4. Count of unread notifications
    try {
        $t0 = microtime(true);
        $unread = \DB::table('notifications')->whereNull('read_at')->count();
        $t1 = microtime(true);
        $results['notifications_unread_count'] = $unread;
        $results['notifications_unread_query_time'] = number_format(($t1 - $t0) * 1000, 2) . ' ms';
    } catch (\Exception $e) {
        $results['notifications_unread_error'] = $e->getMessage();
    }
    
    return response()->json($results);
});


Route::get('/run-migration-debug', function() {
    // Extend execution time limit as table is huge (600k+ rows)
    @set_time_limit(300);
    @ini_set('max_execution_time', 300);
    
    try {
        $output = '';
        $exitCode = Artisan::call('migrate', ['--force' => true]);
        $output .= Artisan::output();
        return response()->json([
            'status' => 'success',
            'exit_code' => $exitCode,
            'output' => $output
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'failed',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});




Route::controller(IndexController::class)->group(function () {


    Route::get('/', 'showSoon')->name('show.home.index');
    // Route::get('/home', 'showHome')->name('show.home.old');
    Route::get('/ranking', 'showRanking')->name('show.user.ranking');



     Route::get('/privacy', 'showPrivacy')->name('privacy.policy');

          Route::get('/group/invitation/{code_number}', 'groupJoin')->name('group.join');
          Route::get('/group/invitation/{code_number}/steps', 'groupJoinSteps')->name('group.join.steps');

        //   Route::get('/group/invite/{code_number}/{user_id}', 'groupJoin')->name('group.join');



});


// Route::get('/dashboard', function () {
//     return view('admin.index');
// })->middleware(['auth', 'verified','checkUserRole'])->name('dashboard');


Route::controller(DashboardController::class)->middleware(['auth', 'verified','checkUserRole'])->group(function () {


    Route::get('/dashboard', 'showDashboard')->name('dashboard');


});

// Route::controller(NotificationController::class)->middleware(['checkUserRole','auth'])->group(function () {


//     // Route::get('/add/notification', 'sendNotification')->name('send.notification');
//     // Route::get('/all/notification', 'alldNotification')->name('all.notification');

// Route::get('/notifications/load',  'loadNotifications')->name('notifications.load');

// });

Route::get('/notifications/load', [NotificationController::class, 'loadMore'])
    ->name('notifications.load')
    ->middleware('auth');

Route::get('/notification/read_simple/{notification}', [NotificationController::class, 'read'])
    ->name('notification.read_simple')
    ->middleware('auth');

Route::controller(NotificationForAppController::class)->middleware(['checkUserRole','auth'])->group(function () {


    // Route::get('/add/notification', 'sendNotification')->name('send.notification');
    // Route::get('/all/notification', 'alldNotification')->name('all.notification');



    Route::get('/add/notification', 'sendNotification')->name('send.notification');
        Route::post('/add/notification', 'sendNotificationStore')->name('send.notification.store');

    Route::get('/all/notification', 'alldNotification')->name('all.notification');
    Route::get('/test-notification', 'sendPush')->name('send.push');


        Route::get('/notification/delete/{id}', 'deleteNotification')->name('delete.notification');

                // Route::get('/notification/delete/all', 'deleteAllNotificationCpanel')->name('delete.all.notification.new');

    Route::get('/delete/all/notifcations', 'deleteAllNotifications')->name('delete.notification.all');



});





Route::controller(AppVersionController::class)->middleware(['checkUserRole','auth'])->group(function () {


    Route::get('/add/versions', 'addVersions')->name('add.versions');
    Route::post('/update/versions', 'updateVersions')->name('update.versions.store');


});




Route::controller(PositionController::class)->middleware(['checkUserRole','auth'])->group(function () {


    // Route::get('/add/position', 'addVersions')->name('add.versions');
    // Route::post('/update/versions', 'updateVersions')->name('update.versions.store');


            Route::get('add/position/user', 'addPositionUser')->name('add.position.user');
            Route::post('add/position/user', 'addPositionUserStore')->name('add.position.user.store');

                Route::get('/search/position', 'searchPosition')->name('search.position');
                Route::post('/search/position/result', 'searchPositionResult')->name('search.position.result');



                // Route::get('/get-rounds/{festival}', 'getRounds')->name('get.round');





});








Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('/admin/logout', [AdminController::class, 'destroy'])->name('admin.logout');




});


Route::controller(AdminController::class)
->middleware(['checkUserRole','auth'])
->group(function () {
    // Route::get('/admin/logout', 'destroy')->name('admin.logout');

    Route::get('/admin/profile', 'adminProfile')->name('admin.profile');

    Route::post('/admin/profile', 'adminProfileStore')->name('admin.profile.store');

    Route::get('/admin/change/password', 'AdminChangePassword')->name('admin.change.password');


    Route::post('/admin/update/password', 'AdminUpdatePassword')->name('update.password');







});


Route::controller(CategoryController::class)->middleware(['checkUserRole','auth'])->group(function () {
    Route::get('/admin/category', 'category')->name('all.category');

    Route::get('/admin/add/category', 'addCategory')->name('add.category');


    Route::post('/add/category' , 'storeCategory')->name('add.category.store');

    Route::get('/admin/edit/category/{id}', 'editCategort')->name('edit.category');

    Route::post('/edit/category' , 'editCategortStore')->name('edit.category.store');


    Route::get('/delete/category/{id}' , 'deleteCategory')->name('delete.category');

    Route::get('/category/inactive/{id}', 'categoryInactive')->name('inactive.category');


    Route::get('/category/active/{id}', 'categoryActive')->name('active.category');



});






Route::controller(FestivalController::class)->middleware(['checkUserRole','auth'])->group(function () {
    Route::get('/all/festival', 'allFestival')->name('all.festival');

    Route::get('/add/festival', 'addFestival')->name('add.festival');


    Route::post('/add/festival' , 'storeFestival')->name('add.festival.store');

    Route::get('/edit/festival/{id}', 'editFestival')->name('edit.festival');

    Route::post('/edit/festival' , 'updateFestival')->name('update.festival');


    Route::get('/delete/festival/{id}' , 'deleteFestival')->name('delete.festival');

    Route::get('/festival/inactive/{id}', 'festivalInactive')->name('inactive.festival');


    Route::get('/festival/active/{id}', 'festivalActive')->name('active.festival');



});

Route::controller(GroubController::class)->middleware(['checkUserRole','auth'])->group(function () {
    Route::get('/all/groub', 'allGroub')->name('all.groub');

    // Route::get('/add/festival', 'addFestival')->name('add.festival');


    // Route::post('/add/festival' , 'storeFestival')->name('add.festival.store');

    // Route::get('/edit/festival/{id}', 'editFestival')->name('edit.festival');

    // Route::post('/edit/festival' , 'updateFestival')->name('update.festival');


    Route::get('/delete/groub/{id}' , 'deleteGroub')->name('delete.groub');

    Route::get('/groub/inactive/{id}', 'groubInactive')->name('inactive.groub');


    Route::get('/groub/active/{id}', 'groubActive')->name('active.groub');


    Route::get('/groub/details/{id}', 'groubDetails')->name('groub.details');



});






Route::controller(RoundController::class)
    ->middleware(['checkUserRole','auth'])
    ->group(function () {

        Route::get('/all/round', 'allRound')->name('all.round');

        Route::get('/add/round', 'addRound')->name('add.round');

        Route::post('/add/round', 'storeRound')->name('add.round.store');

        Route::get('/edit/round/{id}', 'editRound')->name('edit.round');

        Route::post('/edit/round', 'updateRound')->name('update.round');

        Route::get('/delete/round/{id}', 'deleteRound')->name('delete.round');

        Route::get('/round/inactive/{id}', 'roundInactive')->name('inactive.round');

        Route::get('/round/active/{id}', 'roundActive')->name('active.round');
        // Route::get('/get-camals/{gender}', 'getCamals')->name('get.camals');



          Route::get('/round/close/{id}', 'roundClose')->name('close.round');

        Route::get('/round/open/{id}', 'roundOpen')->name('open.round');




        Route::get('/get-camals/{gender}', 'getCamals')->name('get.camals');
// Route::get('/get-camals/{gender}', [CamalController::class, 'getCamals']);


    });


Route::controller(FestivalPointController::class)
    ->middleware(['checkUserRole','auth'])
    ->group(function () {
        Route::get('/festival-points', 'index')->name('all.festival.points');
        Route::get('/festival-points/add', 'create')->name('add.festival.points');
        Route::post('/festival-points/store', 'store')->name('store.festival.points');
        Route::get('/festival-points/edit/{id}', 'edit')->name('edit.festival.points');
        Route::post('/festival-points/update/{id}', 'update')->name('update.festival.points');
        Route::get('/festival-points/delete/{id}', 'destroy')->name('delete.festival.points');
    });





Route::controller(UserController::class)->middleware(['checkUserRole','auth'])->group(function () {
    Route::get('/users/all', 'getAllUsers')->name('all.users');

        Route::get('/owners/all', 'getAllOwners')->name('all.owners');
        Route::get('/admin/all', 'getAllAdmin')->name('all.admin');

    Route::get('/user/add', 'addUser')->name('add.user');

    Route::post('/user/add', 'addUserStore')->name('add.user.store');




    Route::get('/user/edit/{id}', 'editUser')->name('edit.user');

    Route::post('/user/edit', 'editUserStore')->name('edit.user.store');



    Route::get('/user/inactive/{id}', 'userInactive')->name('inactive.user');


    Route::get('/user/active/{id}', 'userActive')->name('active.user');


    Route::get('/user/delete/{id}', 'deleteUser')->name('delete.user');









});



Route::controller(CamelWorkerController::class)->middleware(['checkUserRole','auth'])->group(function () {
    Route::get('/camel-workers/all', 'index')->name('all.camel.workers');
    Route::get('/camel-workers/add', 'create')->name('add.camel.worker');
    Route::post('/camel-workers/store', 'store')->name('store.camel.worker');
    Route::get('/camel-workers/edit/{id}', 'edit')->name('edit.camel.worker');
    Route::post('/camel-workers/update', 'update')->name('update.camel.worker');
    Route::get('/camel-workers/delete/{id}', 'destroy')->name('delete.camel.worker');
    Route::get('/camel-workers/inactive/{id}', 'inactive')->name('inactive.camel.worker');
    Route::get('/camel-workers/active/{id}', 'active')->name('active.camel.worker');
});

Route::controller(TrainingSessionController::class)->middleware(['checkUserRole','auth'])->group(function () {
    Route::get('/training-sessions/all', 'index')->name('all.training.sessions');
    Route::get('/training-sessions/details/{id}', 'show')->name('details.training.session');
    Route::get('/training-sessions/delete/{id}', 'destroy')->name('delete.training.session');
    Route::get('/training-sessions/details/{id}/simulate-ping', 'simulatePing')->name('details.training.session.simulate');
    Route::get('/training-sessions/details/{id}/clear-logs', 'clearLogs')->name('details.training.session.clearLogs');
});



Route::controller(NewsController::class)
    ->middleware(['checkUserRole','auth'])
    ->group(function () {

        // عرض كل الأخبار
        Route::get('/all/news', 'allNews')->name('all.news');

        // صفحة إضافة خبر جديد
        Route::get('/add/news', 'addNews')->name('add.news');

        // تخزين خبر جديد
        Route::post('/add/news', 'storeNews')->name('add.news.store');

        // صفحة تعديل الخبر
        Route::get('/edit/news/{id}', 'editNews')->name('edit.news');

        // تحديث الخبر
        Route::post('/edit/news', 'editNewsStore')->name('edit.news.store');

        // حذف الخبر
        Route::get('/delete/news/{id}', 'deleteNews')->name('delete.news');

        // جعل الخبر غير نشط
        Route::get('/news/inactive/{id}', 'newsInactive')->name('inactive.news');

        // جعل الخبر نشط
        Route::get('/news/active/{id}', 'newsActive')->name('active.news');

});


Route::controller(SliderController::class)
    ->middleware(['checkUserRole','auth'])
    ->group(function () {

        // عرض كل السلايدر
        Route::get('/all/slider', 'allSlider')->name('all.slider');

        // صفحة إضافة سلايدر جديد
        Route::get('/add/slider', 'addSlider')->name('add.slider');

        // تخزين سلايدر جديد
        Route::post('/add/slider', 'storeSlider')->name('add.slider.store');

        // صفحة تعديل السلايدر
        Route::get('/edit/slider/{id}', 'editSlider')->name('edit.slider');

        // تحديث السلايدر
        Route::post('/edit/slider', 'editSliderStore')->name('edit.slider.store');

        // حذف السلايدر
        Route::get('/delete/slider/{id}', 'deleteSlider')->name('delete.slider');

        // جعل السلايدر غير نشط
        Route::get('/slider/inactive/{id}', 'sliderInactive')->name('inactive.slider');

        // جعل السلايدر نشط
        Route::get('/slider/active/{id}', 'sliderActive')->name('active.slider');

    });




    Route::controller(LiveBroadcastController::class)
    ->middleware(['checkUserRole','auth'])
    ->group(function () {

        Route::get('/all/live-broadcast', 'allLiveBroadcast')->name('all.live.broadcast');
        Route::get('/add/live-broadcast', 'addLiveBroadcast')->name('add.live.broadcast');
        Route::post('/add/live-broadcast', 'storeLiveBroadcast')->name('add.live.broadcast.store');
        Route::get('/edit/live-broadcast/{id}', 'editLiveBroadcast')->name('edit.live.broadcast');
        Route::post('/edit/live-broadcast', 'editLiveBroadcastStore')->name('edit.live.broadcast.store');
        Route::get('/delete/live-broadcast/{id}', 'deleteLiveBroadcast')->name('delete.live.broadcast');
        Route::get('/live-broadcast/inactive/{id}', 'liveBroadcastInactive')->name('inactive.live.broadcast');
        Route::get('/live-broadcast/active/{id}', 'liveBroadcastActive')->name('active.live.broadcast');
});



Route::controller(NominationController::class)
    ->middleware(['checkUserRole', 'auth'])
    ->group(function () {

        // عرض كل الترشيحات
        Route::get('/nominations', 'allNomination')->name('all.nomination');

        // إضافة ترشيح جديد
        Route::get('/nominations/add', 'addNomination')->name('add.nomination');
        Route::post('/nominations/store', 'storeNomination')->name('store.nomination');

        // تعديل ترشيح موجود
        Route::get('/nominations/edit/{id}', 'editNomination')->name('edit.nomination');
        Route::post('/nominations/update/{id}', 'updateNomination')->name('update.nomination');

        // حذف ترشيح
        Route::get('/nominations/delete/{id}', 'deleteNomination')->name('delete.nomination');


                        Route::get('/add/round/winner', 'addRoundWinner')->name('add.round.winner');

                        Route::get('/filter/users', 'filterNomination')->name('filter.users');
                        Route::post('/filter/users/store', 'filterNominationStore')->name('filter.users.store');




                        ////
                  Route::get('/filter/users/festival', 'filterNominationFestival')->name('filter.users.fesitval');


                        Route::post('/filter/users/festival/store', 'filterNominationFestivalStore')->name('filter.users.festival.store');

                  ////

        // Route::post('/add/round/winner', 'addRoundWinnerStore')->name('add.round.winner.store');
        Route::post('/add/round/winner/store', 'addRoundWinnerStore')->name('add.round.winner.store');




                Route::get('/get-rounds/{festival}', 'getRounds')->name('get.round');
                                Route::get('/get-rounds/filter/{festival}', 'getRoundsforFilter')->name('get.round.filter');




                Route::get('/get-camals-by-round/{roundId}', 'getCamalsByRound');





        Route::get('add/nominations/user', 'addNominationUser')->name('add.nomination.user');




        Route::post('/nominations/user/store', 'addNominationUserStore')->name('store.nomination.user');





    });


Route::controller(NotificationDashboardController::class)->middleware(['checkUserRole','auth'])->group(function () {



    Route::get('/notification/read/{id}' , 'setNotificationRead')->name('notification.read');

});



Route::controller(CamalController::class)->middleware(['checkUserRole','auth'])->group(function () {
    Route::get('/camal/all', 'getAllCamal')->name('all.camal');


    Route::get('/camal/add', 'addCamal')->name('add.camal');

    Route::post('/camal/add', 'addCamalStore')->name('add.camal.store');




    Route::get('/camal/edit/{id}', 'editCamal')->name('edit.camal');

        Route::get('/camal/all/edit/{owner_id}', 'editAllCamal')->name('edit.all.camal');
        Route::post('/camal/all/edit/store', 'editAllCamalStore')->name('edit.all.camal.store');


    Route::post('/camal/edit', 'editCamalStore')->name('edit.camal.store');



    Route::get('/camal/inactive/{id}', 'camalInactive')->name('inactive.camal');


    Route::get('/camal/active/{id}', 'camalActive')->name('active.camal');


    Route::get('/camal/delete/{id}', 'deleteCamal')->name('delete.camal');









});






Route::controller(GameController::class)->middleware(['checkUserRole','auth'])->group(function () {

    Route::get('/games/all', 'allGames')->name('all.games');

    Route::get('/games/details/{id}', 'detailsGames')->name('details.games');
    Route::get('/games/delete/{id}', 'deleteGame')->name('delete.games');


});




Route::controller(AdsController::class)->middleware(['checkUserRole','auth'])->group(function(){

    Route::get('/add/ads' , 'addAds')->name('add.ads');

    // addAds
});

 // Report All Route
 Route::controller(ReportController::class)->middleware(['checkUserRole','auth'])->group(function(){

    Route::get('/report/view' , 'ReportView')->name('report.view');


    Route::post('/search/by/date' , 'SearchByDate')->name('search-by-date');

    Route::post('/search/by/month' , 'SearchByMonth')->name('search-by-month');
    Route::post('/search/by/year' , 'SearchByYear')->name('search-by-year');

    Route::get('/order/by/user' , 'OrderByUser')->name('order.by.user');
    Route::post('/search/by/user' , 'SearchByUser')->name('search-by-user');


});

Route::controller(QuestionController::class)->middleware(['checkUserRole','auth'])->group(function () {

    Route::get('/admin/add/question', 'addQuestion')->name('add.question');
    Route::post('/admin/add/question', 'addQuestionStore')->name('add.question.store');


    Route::get('/admin/all/question', 'allQuestion')->name('all.question');


    Route::get('/admin/edit/question/{id}', 'editQuestion')->name('edit.question');


    Route::post('/admin/edit/question', 'editQuestionStore')->name('edit.question.store');


    Route::get('/question/delete/{id}', 'deleteQuestion')->name('delete.question');









});


// Coupon controller

 Route::controller(CouponController::class)->middleware(['checkUserRole','auth'])->group(function(){




    ///

         Route::get('/all/coupon', 'AllCoupon')->name('all.coupon');
        Route::get('/add/coupon', 'AddCoupon')->name('add.coupon');
        Route::post('/store/coupon', 'StoreCoupon')->name('store.coupon');

        Route::get('/edit/coupon/{id}', 'EditCoupon')->name('edit.coupon');
        Route::post('/update/coupon', 'UpdateCoupon')->name('update.coupon');
        Route::get('/delete/coupon/{id}', 'DeleteCoupon')->name('delete.coupon');


});


/// price

 Route::controller(PriceController::class)->middleware(['checkUserRole','auth'])->group(function(){




    ///

         Route::get('/all/price', 'allPrice')->name('all.price');
        Route::get('/add/price', 'addPrice')->name('add.price');
        Route::post('/add/price', 'addPriceStore')->name('add.price.store');

                Route::get('/delete/price/{id}', 'deletePrice')->name('delete.price');
        Route::get('/edit/price/{id}', 'editPrice')->name('edit.price');



        Route::post('/edit/price', 'editPriceStore')->name('edit.price.store');


});



Route::middleware(['checkUserRole','auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



 Route::controller(SponsorController::class)->middleware(['checkUserRole','auth'])->group(function(){


        Route::get('/add/sponsor/new' , 'addSponsorNew')->name('sponsor.add.new');

    Route::get('/edit/sponsor/{id}' , 'editSponsor')->name('edit.sponsor');
        Route::get('/add/sponsor/question' , 'addSponsorQuestion')->name('sponsor.add.question');


        Route::post('/edit/sponsor/home/cate', 'editHomeCateStore')->name('edit.home.cate.store');

        Route::post('/add/sponsor/new', 'addSponsorStore')->name('add.sponsor.new');

        Route::get('/all/sponsor' , 'allSponsor')->name('sponsor.all');

                Route::get('/delete/sponsor/{id}', 'deleteSponsor')->name('delete.sponsor');


    // Route::post('/search/by/date' , 'SearchByDate')->name('search-by-date');

    // Route::post('/search/by/month' , 'SearchByMonth')->name('search-by-month');
    // Route::post('/search/by/year' , 'SearchByYear')->name('search-by-year');

    // Route::get('/order/by/user' , 'OrderByUser')->name('order.by.user');
    // Route::post('/search/by/user' , 'SearchByUser')->name('search-by-user');


});




Route::controller(PrizeController::class)
    ->middleware(['checkUserRole','auth'])
    ->group(function () {
        Route::get('/all/prizes', 'allPrizes')->name('all.prizes');
        Route::get('/add/prize', 'addPrize')->name('add.prize');
        Route::post('/add/prize', 'storePrize')->name('add.prize.store');
        Route::get('/edit/prize/{id}', 'editPrize')->name('edit.prize');
        Route::post('/edit/prize', 'editPrizeStore')->name('edit.prize.store');
        Route::get('/delete/prize/{id}', 'deletePrize')->name('delete.prize');
        Route::get('/prize/inactive/{id}', 'prizeInactive')->name('inactive.prize');
        Route::get('/prize/active/{id}', 'prizeActive')->name('active.prize');
});



Route::controller(QuestionAIController::class)->middleware(['checkUserRole','auth'])->group(function () {

    Route::get('/admin/add/question/ai', 'addQuestionAi')->name('add.question.ai');
    Route::post('/admin/add/question/to/game/ai', 'addQuestionToGameAi')->name('add.question.to.game.ai');


    Route::post('/admin/get/question/ai', 'getdQuestionStoreAi')->name('get.question.store.ai');


    Route::get('/admin/all/question/ai', 'allQuestionAi')->name('all.question.ai');


    Route::get('/admin/edit/question/ai/{id}', 'editQuestionAi')->name('edit.question.ai');


    Route::post('/admin/edit/question/ai', 'editQuestionStoreAi')->name('edit.question.store.ai');


    Route::get('/question/delete/ai/{id}', 'deleteQuestionAi')->name('delete.question.ai');









});

// Subscription plans and user subscriptions management
Route::controller(SubscriptionPlanController::class)->middleware(['checkUserRole','auth'])->group(function () {
    Route::get('/subscription-plans/all', 'index')->name('all.subscription.plans');
    Route::get('/subscription-plans/add', 'create')->name('add.subscription.plan');
    Route::post('/subscription-plans/store', 'store')->name('store.subscription.plan');
    Route::get('/subscription-plans/edit/{id}', 'edit')->name('edit.subscription.plan');
    Route::post('/subscription-plans/update', 'update')->name('update.subscription.plan');
    Route::get('/subscription-plans/delete/{id}', 'destroy')->name('delete.subscription.plan');
    Route::get('/subscription-plans/toggle/{id}', 'toggleStatus')->name('toggle.subscription.plan');
});

Route::controller(UserSubscriptionController::class)->middleware(['checkUserRole','auth'])->group(function () {
    Route::get('/user-subscriptions/all', 'index')->name('all.user.subscriptions');
    Route::get('/user-subscriptions/add', 'create')->name('add.user.subscription');
    Route::post('/user-subscriptions/store', 'store')->name('store.user.subscription');
    Route::get('/user-subscriptions/cancel/{id}', 'cancel')->name('cancel.user.subscription');
    Route::get('/user-subscriptions/delete/{id}', 'destroy')->name('delete.user.subscription');
});

Route::get('/payment', [PayMentController::class, 'showPaymentPage']);

require __DIR__.'/auth.php';
