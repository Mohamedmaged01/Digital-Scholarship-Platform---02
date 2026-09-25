<?php

namespace App\Http\Controllers;

use App\Support\SiteSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = mb_substr((string) $request->query('q', ''), 0, 120);

        return response()->json(['results' => SiteSearch::search($q)]);
    }
}
