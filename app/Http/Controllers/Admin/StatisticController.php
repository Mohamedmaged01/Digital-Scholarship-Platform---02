<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataSource;
use App\Models\Statistic;
use App\Support\Showcase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * الأرقام المعروضة في الواجهة. §K: لا يُضاف رقم إلا بمصدر موثّق؛
 * عند خلوّ موقع من الأرقام تعرض الواجهة الأرقام الهيكلية المحسوبة.
 */
class StatisticController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.statistics', [
            'items' => Statistic::orderBy('location')->orderBy('sort')->get()->groupBy('location'),
            'editing' => $request->filled('edit') ? Statistic::find($request->query('edit')) : null,
            'sources' => DataSource::orderBy('name_ar')->get(['id', 'name_ar']),
            'fallback' => [
                'general' => Statistic::where('location', 'general')->doesntExist() ? Showcase::statistics('general') : [],
                'hero' => Statistic::where('location', 'hero')->doesntExist() ? Showcase::statistics('hero') : [],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Statistic::create($this->validated($request));

        return redirect()->route('admin.statistics.index')->with('status', 'تمت إضافة الرقم');
    }

    public function update(Request $request, Statistic $statistic): RedirectResponse
    {
        $statistic->update($this->validated($request));

        return redirect()->route('admin.statistics.index')->with('status', 'تم حفظ التعديلات');
    }

    public function destroy(Statistic $statistic): RedirectResponse
    {
        $statistic->delete();

        return back()->with('status', 'تم حذف الرقم');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label_ar' => ['required', 'string', 'min:2', 'max:255'],
            'label_en' => ['nullable', 'string', 'max:255'],
            'value' => ['required', 'integer', 'min:0'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'note_ar' => ['nullable', 'string', 'max:255'],
            'note_en' => ['nullable', 'string', 'max:255'],
            'location' => ['required', Rule::in(Statistic::LOCATIONS)],
            'source_id' => ['required', 'exists:data_sources,id'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], ['source_id.required' => 'اختر المصدر الموثّق الذي يستند إليه هذا الرقم.']);

        return [
            ...$data,
            'label_en' => $data['label_en'] ?? '',
            'suffix' => $data['suffix'] ?? '',
            'note_ar' => $data['note_ar'] ?? '',
            'note_en' => $data['note_en'] ?? '',
            'sort' => $data['sort'] ?? 0,
            'last_verified_at' => now(),
        ];
    }
}
