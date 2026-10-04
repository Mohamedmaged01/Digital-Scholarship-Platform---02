<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Station;
use App\Models\Track;
use App\Support\Showcase;
use App\Support\SiteSearch;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $tracks = Track::orderBy('sort')->get();

        return view('home', [
            'tracks' => $tracks,
            'stations' => Station::orderBy('sort')->get(),
            'news' => News::ordered()->get(),
            'quickLinks' => SiteSearch::quickLinks(),
            'stats' => Showcase::statistics('general'),
            'heroStats' => Showcase::statistics('hero'),
            'matcher' => Showcase::matcher($tracks),
        ]);
    }
}
