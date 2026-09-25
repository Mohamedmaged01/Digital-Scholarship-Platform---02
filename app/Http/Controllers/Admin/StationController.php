<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Support\DefaultContent;
use App\Support\Lists;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StationController extends Controller
{
    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');

        return view('admin.stations', [
            'items' => Station::orderBy('sort')->get()
                ->filter(fn (Station $s) => Lists::matches($q, "{$s->title} {$s->description} ".implode(' ', $s->points))),
            'editingId' => (int) $request->query('edit'),
            'q' => $q,
        ]);
    }

    public function update(Request $request, Station $station): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'duration' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:4'],
            'detail' => ['nullable', 'string'],
            'points' => ['nullable', 'string', 'max:1000'],
        ]);

        $station->update([
            ...$data,
            'duration' => $data['duration'] ?? '',
            'detail' => $data['detail'] ?? '',
            'points' => Lists::split($data['points'] ?? ''),
        ]);

        return redirect()->route('admin.stations.index')->with('status', 'تم حفظ التعديلات ونشرها مباشرة');
    }

    public function reset(): RedirectResponse
    {
        DefaultContent::resetStations();

        return redirect()->route('admin.stations.index')->with('status', 'تمت استعادة المحطات الافتراضية');
    }
}
