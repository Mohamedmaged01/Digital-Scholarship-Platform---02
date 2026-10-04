<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Support\DefaultContent;
use App\Support\Lists;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UniversityController extends Controller
{
    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');
        $region = (string) $request->query('region', 'all');

        return view('admin.universities', [
            'items' => University::orderBy('name_ar')->get()
                ->filter(fn (University $u) => ($region === 'all' || $u->region === $region)
                    && Lists::matches($q, "{$u->name_en} {$u->name_ar} {$u->city} {$u->country} ".implode(' ', $u->fields))),
            'editing' => $request->filled('edit') ? University::find($request->query('edit')) : null,
            'q' => $q,
            'region' => $region,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        University::create($this->validated($request));

        return redirect()->route('admin.universities.index')->with('status', 'تمت إضافة الجامعة');
    }

    public function update(Request $request, University $university): RedirectResponse
    {
        $university->update($this->validated($request));

        return redirect()->route('admin.universities.index')->with('status', 'تم حفظ التعديلات');
    }

    public function destroy(University $university): RedirectResponse
    {
        $university->delete();

        return back()->with('status', 'تم حذف الجامعة');
    }

    public function reset(): RedirectResponse
    {
        DefaultContent::resetUniversities();

        return redirect()->route('admin.universities.index')->with('status', 'تمت استعادة القائمة الافتراضية');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name_en' => ['required', 'string', 'min:2', 'max:255'],
            'name_ar' => ['required', 'string', 'min:2', 'max:255'],
            'region' => ['required', Rule::in(University::REGIONS)],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'fields' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'url', 'max:500'],
            'institution_status' => ['nullable', Rule::in(University::STATUSES)],
        ]);

        return [
            ...$data,
            'city' => $data['city'] ?? '',
            'country' => $data['country'] ?? '',
            'fields' => Lists::split($data['fields'] ?? ''),
            'website' => $data['website'] ?? '',
            // §J: افتراضيًا كل مؤسسة بحاجة إلى تحقّق حتى يُثبت العكس من مصدر رسمي.
            'institution_status' => $data['institution_status'] ?? 'needs_verification',
        ];
    }
}
