<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\UnansweredQuestion;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $categoryCounts = News::query()->selectRaw('category, count(*) as c')->groupBy('category')->pluck('c', 'category');

        return view('admin.dashboard', [
            'categoryCounts' => collect(config('kasp.news_categories'))
                ->map(fn ($meta, $key) => ['label' => $meta['tab'], 'count' => (int) ($categoryCounts[$key] ?? 0)]),
            'latestNews' => News::ordered()->take(3)->get(),
            'pending' => UnansweredQuestion::orderByDesc('asked_count')->orderByDesc('last_asked_at')->take(3)->get(),
            'dbDriver' => config('database.default'),
        ]);
    }
}
