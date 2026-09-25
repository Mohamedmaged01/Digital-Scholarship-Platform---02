<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

/* ---------------- الواجهة العامة ---------------- */
Route::get('/', HomeController::class)->name('home');

Route::middleware('throttle:40,1')->group(function () {
    Route::post('/assistant/ask', AssistantController::class)->name('assistant.ask');
    Route::get('/search', SearchController::class)->name('search');
    Route::post('/newsletter', NewsletterController::class)->name('newsletter.subscribe');
});

/* ---------------- المصادقة ---------------- */
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1');
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/* ---------------- لوحة التحكم ---------------- */
Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');
    Route::get('/search', Admin\SearchController::class)->name('search');

    // الأخبار والإعلانات
    Route::get('/news', [Admin\NewsController::class, 'index'])->name('news.index');
    Route::middleware('can:edit-content')->group(function () {
        Route::post('/news', [Admin\NewsController::class, 'store'])->name('news.store');
        Route::put('/news/{news}', [Admin\NewsController::class, 'update'])->name('news.update');
        Route::patch('/news/{news}/pin', [Admin\NewsController::class, 'togglePin'])->name('news.pin');
    });
    Route::delete('/news/{news}', [Admin\NewsController::class, 'destroy'])->middleware('can:delete-content')->name('news.destroy');

    // قاعدة المعرفة
    Route::get('/kb', [Admin\KbController::class, 'index'])->name('kb.index');
    Route::middleware('can:edit-content')->group(function () {
        Route::post('/kb', [Admin\KbController::class, 'store'])->name('kb.store');
        Route::put('/kb/{entry}', [Admin\KbController::class, 'update'])->name('kb.update');
    });
    Route::delete('/kb/{entry}', [Admin\KbController::class, 'destroy'])->middleware('can:delete-content')->name('kb.destroy');

    // الأسئلة بلا إجابة
    Route::get('/pending', [Admin\PendingController::class, 'index'])->name('pending.index');
    Route::post('/pending/{question}/answer', [Admin\PendingController::class, 'answer'])->middleware('can:edit-content')->name('pending.answer');
    Route::delete('/pending/{question}', [Admin\PendingController::class, 'destroy'])->middleware('can:edit-content')->name('pending.destroy');

    // الجامعات
    Route::get('/universities', [Admin\UniversityController::class, 'index'])->name('universities.index');
    Route::middleware('can:edit-content')->group(function () {
        Route::post('/universities', [Admin\UniversityController::class, 'store'])->name('universities.store');
        Route::put('/universities/{university}', [Admin\UniversityController::class, 'update'])->name('universities.update');
    });
    Route::middleware('can:delete-content')->group(function () {
        Route::delete('/universities/{university}', [Admin\UniversityController::class, 'destroy'])->name('universities.destroy');
        Route::post('/universities/reset', [Admin\UniversityController::class, 'reset'])->name('universities.reset');
    });

    // المسارات
    Route::get('/tracks', [Admin\TrackController::class, 'index'])->name('tracks.index');
    Route::middleware('can:edit-content')->group(function () {
        Route::post('/tracks', [Admin\TrackController::class, 'store'])->name('tracks.store');
        Route::put('/tracks/{track}', [Admin\TrackController::class, 'update'])->name('tracks.update');
    });
    Route::middleware('can:delete-content')->group(function () {
        Route::delete('/tracks/{track}', [Admin\TrackController::class, 'destroy'])->name('tracks.destroy');
        Route::post('/tracks/reset', [Admin\TrackController::class, 'reset'])->name('tracks.reset');
    });

    // محطات خارطة الطريق
    Route::get('/stations', [Admin\StationController::class, 'index'])->name('stations.index');
    Route::put('/stations/{station}', [Admin\StationController::class, 'update'])->middleware('can:edit-content')->name('stations.update');
    Route::post('/stations/reset', [Admin\StationController::class, 'reset'])->middleware('can:delete-content')->name('stations.reset');

    // مركز الملفات
    Route::get('/files', [Admin\FileController::class, 'index'])->name('files.index');
    Route::post('/files', [Admin\FileController::class, 'store'])->middleware('can:edit-content')->name('files.store');
    Route::delete('/files/{file}', [Admin\FileController::class, 'destroy'])->middleware('can:delete-content')->name('files.destroy');

    // المستخدمون (للمدير فقط)
    Route::middleware('can:manage-users')->group(function () {
        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle', [Admin\UserController::class, 'toggle'])->name('users.toggle');
        Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
    });
});
