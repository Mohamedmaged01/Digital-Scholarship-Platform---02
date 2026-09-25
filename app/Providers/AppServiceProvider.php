<?php

namespace App\Providers;

use App\Models\KbEntry;
use App\Models\ManagedFile;
use App\Models\News;
use App\Models\Station;
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
            ]));
        });
    }
}
