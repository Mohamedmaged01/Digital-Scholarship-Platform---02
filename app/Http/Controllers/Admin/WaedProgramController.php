<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Track;
use App\Support\DefaultContent;
use App\Support\Lists;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** برامج مسار واعد: كل برنامج مستقل بجهته وشروطه ومتطلباته وفترة تقديمه. */
class WaedProgramController extends Controller
{
    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');

        return view('admin.waed', [
            'items' => Program::waed()->orderByDesc('is_featured')->orderBy('sort')->get()
                ->filter(fn (Program $p) => Lists::matches($q, "{$p->name_ar} {$p->name_en} {$p->company_ar} {$p->major_name} {$p->sector}")),
            'editing' => $request->filled('edit') ? Program::waed()->find($request->query('edit')) : null,
            'q' => $q,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $track = Track::where('slug', 'waed')->firstOrFail();
        $data = $this->validated($request);

        $base = Str::slug($data['name_en']) ?: 'program-'.Str::lower(Str::random(6));
        $slug = $base;
        for ($i = 2; Program::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        Program::create($data + [
            'path_id' => $track->id,
            'slug' => $slug,
            'sort' => (int) Program::where('path_id', $track->id)->max('sort') + 1,
        ]);

        return redirect()->route('admin.waed.index')->with('status', 'تمت إضافة البرنامج');
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        abort_unless($program->track?->slug === 'waed', 404);
        $program->update($this->validated($request, $program));

        return redirect()->route('admin.waed.index')->with('status', 'تم حفظ التعديلات');
    }

    public function destroy(Program $program): RedirectResponse
    {
        abort_unless($program->track?->slug === 'waed', 404);
        $program->delete();

        return back()->with('status', 'تم حذف البرنامج');
    }

    public function reset(): RedirectResponse
    {
        DefaultContent::resetWaedPrograms();

        return redirect()->route('admin.waed.index')->with('status', 'تمت استعادة البرامج الافتراضية');
    }

    private function validated(Request $request, ?Program $program = null): array
    {
        $data = $request->validate([
            'name_ar' => ['required', 'string', 'min:4', 'max:255'],
            'name_en' => ['required', 'string', 'max:255', Rule::unique('programs', 'name_en')->ignore($program)],
            'company_ar' => ['required', 'string', 'max:255'],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'country_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'program_type' => ['required', Rule::in(Program::TYPES)],
            'duration' => ['nullable', 'string', 'max:60'],
            'degree_id' => ['required', Rule::in(array_keys(config('kasp.degrees')))],
            'sector' => ['required', 'string', 'max:255'],
            'major_name' => ['nullable', 'string', 'max:255'],
            'required_majors' => ['nullable', 'string', 'max:2000'],
            'gpa_min' => ['nullable', 'string', 'max:10'],
            'gpa_scale' => ['nullable', 'string', 'max:10'],
            'gpa_notes' => ['nullable', 'string', 'max:255'],
            'languages' => ['nullable', 'array'],
            'languages.*.test' => ['nullable', 'string', 'max:40'],
            'languages.*.score' => ['nullable', 'string', 'max:20'],
            'languages.*.notes' => ['nullable', 'string', 'max:255'],
            'tests' => ['nullable', 'array'],
            'tests.*.name' => ['nullable', 'string', 'max:40'],
            'tests.*.score' => ['nullable', 'string', 'max:20'],
            'tests.*.notes' => ['nullable', 'string', 'max:255'],
            'conditions' => ['nullable', 'array'],
            'conditions.*.category' => ['nullable', Rule::in(array_keys(config('kasp.waed_condition_categories')))],
            'conditions.*.text' => ['nullable', 'string', 'max:500'],
            'application_status' => ['required', Rule::in(Program::APPLICATION_STATUSES)],
            'application_start' => ['nullable', 'date'],
            'application_end' => ['nullable', 'date', 'after_or_equal:application_start'],
            'study_start_date' => ['nullable', 'date'],
            'description_ar' => ['nullable', 'string', 'max:3000'],
            'website' => ['nullable', 'url', 'max:500'],
            'version' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // صفوف فارغة في المكرِّرات تُتجاهل
        $rows = fn (string $key, string $required) => collect($data[$key] ?? [])
            ->filter(fn ($r) => filled($r[$required] ?? null))
            ->map(fn ($r) => array_map(fn ($v) => trim((string) $v), $r))
            ->values()->all();

        $conditions = collect($request->input('conditions', []))
            ->filter(fn ($c) => filled($c['text'] ?? null))
            ->map(fn ($c) => [
                'category' => $c['category'] ?? 'general',
                'text' => trim($c['text']),
                'required' => filter_var($c['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ])->values()->all();

        return [
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'],
            'company_ar' => $data['company_ar'],
            'institution_name' => $data['institution_name'] ?? '',
            'country_name' => $data['country_name'] ?? '',
            'city' => $data['city'] ?? '',
            'program_type' => $data['program_type'],
            'duration' => $data['duration'] ?? '',
            'degree_id' => $data['degree_id'],
            'sector' => $data['sector'],
            'major_name' => $data['major_name'] ?? '',
            'required_majors' => Lists::lines($data['required_majors'] ?? ''),
            'gpa' => filled($data['gpa_min'] ?? null)
                ? ['min' => $data['gpa_min'], 'scale' => ($data['gpa_scale'] ?? '') ?: '5.0', 'notes' => $data['gpa_notes'] ?? '']
                : null,
            'languages' => $rows('languages', 'test'),
            'tests' => $rows('tests', 'name'),
            'conditions' => $conditions,
            'application_status' => $data['application_status'],
            'application_start' => $data['application_start'] ?? null,
            'application_end' => $data['application_end'] ?? null,
            'study_start_date' => $data['study_start_date'] ?? null,
            'description_ar' => $data['description_ar'] ?? '',
            'website' => $data['website'] ?? '',
            'version' => ($data['version'] ?? '') ?: '2026-2027',
            'is_featured' => $request->boolean('is_featured'),
            'notes' => $data['notes'] ?? '',
        ];
    }
}
