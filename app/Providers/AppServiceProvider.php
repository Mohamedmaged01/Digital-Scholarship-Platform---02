<?php

namespace App\Providers;

use App\Models\ContactMethod;
use App\Models\Guide;
use App\Models\KbEntry;
use App\Models\LegalPage;
use App\Models\ManagedFile;
use App\Models\News;
use App\Models\Program;
use App\Models\Station;
use App\Models\Statistic;
use App\Models\Track;
use App\Models\UnansweredQuestion;
use App\Models\University;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /* الصلاحيات: المدير كل شيء، المحرّر إنشاء/تعديل، المطّلع استعراض فقط */
        Gate::define('edit-content', fn (User $user) => $user->canEditContent());
        Gate::define('delete-content', fn (User $user) => $user->canDeleteContent());
        Gate::define('manage-users', fn (User $user) => $user->canDeleteContent());

        // وسائل الاتصال والصفحات القانونية — تُدار من اللوحة وتظهر في التذييل والمحادثة
        View::composer(['components.chat-panel', 'partials.site-footer'], function ($view) {
            $view->with('contactMethods', once(fn () => ContactMethod::orderBy('sort')->get()));
            $view->with('legalPages', once(fn () => LegalPage::orderBy('sort')->get(['slug', 'title_ar'])));
            $view->with('footerTracks', once(fn () => Track::orderBy('sort')->get(['id', 'slug', 'name', 'badge'])));
        });

        // عدّادات الشريط الجانبي في لوحة التحكم
        View::composer(['layouts.admin', 'admin.*'], function ($view) {
            $view->with('sectionCounts', once(fn () => [
                'news' => News::count(),
                'kb' => KbEntry::count(),
                'pending' => UnansweredQuestion::count(),
                'universities' => University::count(),
                'tracks' => Track::count(),
                'stations' => Station::count(),
                'files' => ManagedFile::count(),
                'users' => User::count(),
                'guides' => Guide::count(),
                'waed' => Program::waed()->count(),
                'statistics' => Statistic::count(),
                'contact' => ContactMethod::count(),
                'pages' => LegalPage::count(),
            ]));
        });
    }
}
