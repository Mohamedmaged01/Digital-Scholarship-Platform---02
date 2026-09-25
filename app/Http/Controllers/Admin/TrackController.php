<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Support\DefaultContent;
use App\Support\Lists;
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
            'gpa' => ['nullable', 'string', 'max:50'],
            'ranking' => ['nullable', 'string', 'max:100'],
            'fields' => ['nullable', 'string', 'max:1000'],
            'extra_fields' => ['nullable', 'integer', 'min:0', 'max:99'],
            'icon' => ['required', Rule::in(Track::ICONS)],
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
            'gpa' => $data['gpa'] ?? '',
            'ranking' => $data['ranking'] ?? '',
            'fields' => Lists::split($data['fields'] ?? ''),
            'extra_fields' => $data['extra_fields'] ?? 0,
            'perks' => Lists::lines($data['perks'] ?? ''),
            'image' => ($data['image'] ?? '') ?: '/images/tracks/rowad.jpg',
            'degrees' => array_values(array_intersect(Track::DEGREES, $data['degrees'])),
        ];
    }
}
