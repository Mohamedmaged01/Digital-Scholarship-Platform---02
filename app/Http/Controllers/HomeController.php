<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Station;
use App\Models\Track;
use App\Models\University;
use App\Support\SiteSearch;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'tracks' => Track::orderBy('sort')->get(),
            'universities' => University::orderBy('rank')->get(),
            'stations' => Station::orderBy('sort')->get(),
            'news' => News::ordered()->get(),
            'quickLinks' => SiteSearch::quickLinks(),
        ]);
    }
}
