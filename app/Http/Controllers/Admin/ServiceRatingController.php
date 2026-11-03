<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRating;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** تقييمات أداء الخدمة: ملخص النِّسب وقائمة التقييمات مع الملاحظات. */
class ServiceRatingController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate(['rating' => ['nullable', Rule::in(ServiceRating::RATINGS)]])['rating'] ?? null;
        $counts = ServiceRating::selectRaw('rating, count(*) as total')->groupBy('rating')->pluck('total', 'rating');

        return view('admin.ratings', [
            'counts' => collect(ServiceRating::RATINGS)->mapWithKeys(fn ($r) => [$r => (int) ($counts[$r] ?? 0)]),
            'total' => (int) $counts->sum(),
            'items' => ServiceRating::when($filter, fn ($q) => $q->where('rating', $filter))->latest()->limit(300)->get(),
            'filter' => $filter,
        ]);
    }
}
