<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\Models\Track;
use App\Support\Showcase;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** صفحة المسار التفصيلية: المستكشف (درجة ← تخصص ← جامعات) والأدلة الاسترشادية. */
class TrackController extends Controller
{
    public function show(Track $track): View|RedirectResponse
    {
        // مسار واعد يعمل بنظام البرامج المستقلة
        if ($track->slug === 'waed') {
            return redirect()->route('waed.index');
        }

        $guides = Guide::where('path_id', $track->id)->where('status', '!=', 'draft')
            ->orderByDesc('is_current')->orderByDesc('publication_date')->get();

        return view('tracks.show', [
            'track' => $track,
            'explorer' => Showcase::explorer($track),
            'currentGuides' => $guides->filter(fn (Guide $g) => $g->is_current && $g->status === 'active'),
            'pastGuides' => $guides->reject(fn (Guide $g) => $g->is_current && $g->status === 'active'),
            'otherTracks' => Track::where('id', '!=', $track->id)->orderBy('sort')->get(['id', 'slug', 'name']),
        ]);
    }
}
