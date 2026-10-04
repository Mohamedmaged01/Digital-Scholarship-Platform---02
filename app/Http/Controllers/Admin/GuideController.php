<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataSource;
use App\Models\Guide;
use App\Models\ManagedFile;
use App\Models\Track;
use App\Support\DefaultContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** الأدلة الاسترشادية لكل مسار: رابط أو ملف من مركز الملفات، بإصدارات. */
class GuideController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.guides', [
            'tracks' => Track::orderBy('sort')->get(),
            'guides' => Guide::with('file')->orderByDesc('is_current')->orderByDesc('publication_date')->get()->groupBy('path_id'),
            'editing' => $request->filled('edit') ? Guide::find($request->query('edit')) : null,
            'files' => ManagedFile::latest()->get(['id', 'name']),
            'sources' => DataSource::orderBy('name_ar')->get(['id', 'name_ar']),
            'preselectTrack' => (int) $request->query('track'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $guide = Guide::create($this->validated($request));
        $this->demoteOthers($guide);

        return redirect()->route('admin.guides.index')->with('status', 'تمت إضافة الدليل');
    }

    public function update(Request $request, Guide $guide): RedirectResponse
    {
        $guide->update($this->validated($request));
        $this->demoteOthers($guide);

        return redirect()->route('admin.guides.index')->with('status', 'تم حفظ التعديلات');
    }

    public function destroy(Guide $guide): RedirectResponse
    {
        $guide->delete();

        return back()->with('status', 'تم حذف الدليل');
    }

    public function reset(): RedirectResponse
    {
        DefaultContent::resetGuides();

        return redirect()->route('admin.guides.index')->with('status', 'تمت استعادة الأدلة الافتراضية');
    }

    /** دليل حالي واحد لكل مسار: اعتماد إصدار جديد يحوّل السابق إلى «سابق». */
    private function demoteOthers(Guide $guide): void
    {
        if ($guide->is_current && $guide->path_id) {
            Guide::where('path_id', $guide->path_id)->whereKeyNot($guide->id)->update(['is_current' => false]);
        }
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'path_id' => ['required', 'exists:tracks,id'],
            'title_ar' => ['required', 'string', 'min:4', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'guide_type' => ['required', Rule::in(Guide::TYPES)],
            'url' => ['nullable', 'url', 'max:500', 'required_without:file_id'],
            'file_id' => ['nullable', 'exists:managed_files,id'],
            'version' => ['nullable', 'string', 'max:20'],
            'publication_date' => ['nullable', 'date'],
            'effective_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(Guide::STATUSES)],
            'source_id' => ['nullable', 'exists:data_sources,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], ['url.required_without' => 'أدخل رابط الدليل أو اختر ملفًا من مركز الملفات.']);

        return [
            ...$data,
            'title_en' => $data['title_en'] ?? '',
            'url' => $data['url'] ?? '',
            'version' => ($data['version'] ?? '') ?: '2026-2027',
            'is_current' => $request->boolean('is_current'),
        ];
    }
}
