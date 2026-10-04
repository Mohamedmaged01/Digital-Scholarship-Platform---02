<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\Models\Program;
use App\Models\Track;
use Illuminate\View\View;

/** صفحة مسار واعد: برامج القطاعات الوطنية الحالية والسابقة. */
class WaedController extends Controller
{
    public function index(): View
    {
        $track = Track::where('slug', 'waed')->firstOrFail();
        $programs = Program::where('path_id', $track->id)->orderByDesc('is_featured')->orderBy('sort')->get();

        return view('waed.index', [
            'track' => $track,
            'current' => $programs->reject->isPast()->values(),
            'past' => $programs->filter->isPast()->values(),
            'programs' => $programs,
            'guide' => Guide::where('path_id', $track->id)->where('is_current', true)->where('status', 'active')->first(),
        ]);
    }

    public function show(Program $program): View
    {
        abort_unless($program->track?->slug === 'waed', 404);

        return view('waed.show', ['program' => $program]);
    }
}
