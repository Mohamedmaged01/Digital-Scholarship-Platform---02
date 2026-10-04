<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\WaedController;
use Illuminate\Support\Facades\Route;

/* ---------------- الواجهة العامة ---------------- */
Route::get('/', HomeController::class)->name('home');
Route::get('/tracks/{track:slug}', [TrackController::class, 'show'])->name('tracks.show');
Route::get('/waed', [WaedController::class, 'index'])->name('waed.index');
Route::get('/waed/{program:slug}', [WaedController::class, 'show'])->name('waed.show');
Route::get('/pages/{page}', [LegalPageController::class, 'show'])->name('pages.show');

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

    // الأدلة الاسترشادية
    Route::get('/guides', [Admin\GuideController::class, 'index'])->name('guides.index');
    Route::middleware('can:edit-content')->group(function () {
        Route::post('/guides', [Admin\GuideController::class, 'store'])->name('guides.store');
        Route::put('/guides/{guide}', [Admin\GuideController::class, 'update'])->name('guides.update');
    });
    Route::middleware('can:delete-content')->group(function () {
        Route::delete('/guides/{guide}', [Admin\GuideController::class, 'destroy'])->name('guides.destroy');
        Route::post('/guides/reset', [Admin\GuideController::class, 'reset'])->name('guides.reset');
    });

    // برامج مسار واعد
    Route::get('/waed', [Admin\WaedProgramController::class, 'index'])->name('waed.index');
    Route::middleware('can:edit-content')->group(function () {
        Route::post('/waed', [Admin\WaedProgramController::class, 'store'])->name('waed.store');
        Route::put('/waed/{program}', [Admin\WaedProgramController::class, 'update'])->name('waed.update');
    });
    Route::middleware('can:delete-content')->group(function () {
        Route::delete('/waed/{program}', [Admin\WaedProgramController::class, 'destroy'])->name('waed.destroy');
        Route::post('/waed/reset', [Admin\WaedProgramController::class, 'reset'])->name('waed.reset');
    });

    // إدارة العلاقات: مسار ← درجة ← تخصص ← جامعة (للمدير فقط)
    Route::middleware('can:manage-users')->prefix('relations')->name('relations.')->group(function () {
        Route::get('/', [Admin\RelationController::class, 'index'])->name('index');
        Route::put('/{track:slug}/majors', [Admin\RelationController::class, 'updateMajors'])->name('majors');
        Route::put('/{track:slug}/institutions', [Admin\RelationController::class, 'updateInstitutions'])->name('institutions');
        Route::patch('/{track:slug}/verify', [Admin\RelationController::class, 'verify'])->name('verify');
        Route::post('/majors', [Admin\RelationController::class, 'storeMajor'])->name('majors.store');
        Route::post('/reset', [Admin\RelationController::class, 'reset'])->name('reset');
    });

    // الإحصاءات والأرقام
    Route::get('/statistics', [Admin\StatisticController::class, 'index'])->name('statistics.index');
    Route::middleware('can:edit-content')->group(function () {
        Route::post('/statistics', [Admin\StatisticController::class, 'store'])->name('statistics.store');
        Route::put('/statistics/{statistic}', [Admin\StatisticController::class, 'update'])->name('statistics.update');
    });
    Route::delete('/statistics/{statistic}', [Admin\StatisticController::class, 'destroy'])->middleware('can:delete-content')->name('statistics.destroy');

    // وسائل الاتصال
    Route::get('/contact', [Admin\ContactMethodController::class, 'index'])->name('contact.index');
    Route::middleware('can:edit-content')->group(function () {
        Route::post('/contact', [Admin\ContactMethodController::class, 'store'])->name('contact.store');
        Route::put('/contact/{contact}', [Admin\ContactMethodController::class, 'update'])->name('contact.update');
    });
    Route::middleware('can:delete-content')->group(function () {
        Route::delete('/contact/{contact}', [Admin\ContactMethodController::class, 'destroy'])->name('contact.destroy');
        Route::post('/contact/reset', [Admin\ContactMethodController::class, 'reset'])->name('contact.reset');
    });

    // السياسات والشروط
    Route::get('/pages', [Admin\LegalPageController::class, 'index'])->name('pages.index');
    Route::middleware('can:edit-content')->group(function () {
        Route::post('/pages', [Admin\LegalPageController::class, 'store'])->name('pages.store');
        Route::put('/pages/{page:id}', [Admin\LegalPageController::class, 'update'])->name('pages.update');
    });
    Route::middleware('can:delete-content')->group(function () {
        Route::delete('/pages/{page:id}', [Admin\LegalPageController::class, 'destroy'])->name('pages.destroy');
        Route::post('/pages/reset', [Admin\LegalPageController::class, 'reset'])->name('pages.reset');
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
