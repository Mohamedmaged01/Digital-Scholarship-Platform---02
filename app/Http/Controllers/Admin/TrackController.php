<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Support\DefaultContent;
use App\Support\Lists;
use App\Support\TrackContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TrackController extends Controller
{
    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');

        return view('admin.tracks', [
            'items' => Track::orderBy('sort')->get()
                ->filter(fn (Track $t) => Lists::matches($q, "{$t->name} {$t->en_subtitle} {$t->badge} ".implode(' ', $t->fields))),
            'editing' => $request->filled('edit') ? Track::find($request->query('edit')) : null,
            'q' => $q,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $next = (int) Track::max('sort') + 1;

        Track::create([
            ...$data,
            'slug' => Str::slug($data['en_subtitle']).'-'.Str::lower(Str::random(4)),
            'code' => $data['code'] ?: str_pad((string) $next, 2, '0', STR_PAD_LEFT),
            'sort' => $next,
        ]);

        return redirect()->route('admin.tracks.index')->with('status', 'تمت إضافة المسار');
    }

    public function update(Request $request, Track $track): RedirectResponse
    {
        $data = $this->validated($request);
        $track->update([...$data, 'code' => $data['code'] ?: $track->code]);

        return redirect()->route('admin.tracks.index')->with('status', 'تم حفظ التعديلات');
    }

    public function destroy(Track $track): RedirectResponse
    {
        $track->delete();

        return back()->with('status', 'تم حذف المسار');
    }

    public function reset(): RedirectResponse
    {
        DefaultContent::resetTracks();

        return redirect()->route('admin.tracks.index')->with('status', 'تمت استعادة المسارات الافتراضية');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'code' => ['nullable', 'string', 'max:10'],
            'badge' => ['nullable', 'string', 'max:255'],
            'en_subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:8'],
            'application_status' => ['nullable', Rule::in(Track::APPLICATION_STATUSES)],
            'application_start' => ['nullable', 'date'],
            'application_end' => ['nullable', 'date', 'after_or_equal:application_start'],
            'ranking' => ['nullable', 'string', 'max:100'],
            'fields' => ['nullable', 'string', 'max:1000'],
            'icon' => ['required', Rule::in(Track::ICONS)],
            'tier' => ['nullable', Rule::in(Track::TIERS)],
            'majors_scope' => ['nullable', Rule::in(Track::MAJORS_SCOPES)],
            'match_fields' => ['nullable', 'array'],
            'match_fields.*' => ['string', 'exists:fields,id'],
            'institutions_count' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'external_url' => ['nullable', 'url:https', 'max:500'],
            'details' => ['nullable', 'string', 'max:10000'],
            'policies' => ['nullable', 'string', 'max:20000'],
            'general_conditions' => ['nullable', 'string', 'max:20000'],
            'additional_conditions' => ['nullable', 'string', 'max:20000'],
            'special_conditions' => ['nullable', 'string', 'max:40000'],
            'faqs' => ['nullable', 'string', 'max:20000'],
            'perks' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'string', 'max:500'],
            'degrees' => ['required', 'array', 'min:1'],
            'degrees.*' => [Rule::in(Track::DEGREES)],
        ]);

        return [
            ...$data,
            'code' => $data['code'] ?? '',
            'badge' => $data['badge'] ?? '',
            'en_subtitle' => $data['en_subtitle'] ?? '',
            'application_status' => $data['application_status'] ?? 'not_started',
            'ranking' => $data['ranking'] ?? '',
            'fields' => Lists::split($data['fields'] ?? ''),
            // ملاحظة 35: لا أرقام «+N تخصص» ثابتة
            'extra_fields' => 0,
            'tier' => $data['tier'] ?? 'main',
            'majors_scope' => $data['majors_scope'] ?? 'listed',
            'match_fields' => array_values($data['match_fields'] ?? []) ?: null,
            'institutions_count' => $data['institutions_count'] ?? null,
            'external_url' => $data['external_url'] ?? '',
            'details' => TrackContent::parseDetails($data['details'] ?? ''),
            'policies' => TrackContent::parseSections($data['policies'] ?? ''),
            'general_conditions' => TrackContent::parseSections($data['general_conditions'] ?? ''),
            'additional_conditions' => TrackContent::parseSections($data['additional_conditions'] ?? ''),
            'special_conditions' => TrackContent::parseSections($data['special_conditions'] ?? ''),
            'faqs' => TrackContent::parseFaqs($data['faqs'] ?? ''),
            'perks' => Lists::lines($data['perks'] ?? ''),
            'image' => ($data['image'] ?? '') ?: '/images/tracks/rowad.jpg',
            'degrees' => array_values(array_intersect(Track::DEGREES, $data['degrees'])),
        ];
    }
}
