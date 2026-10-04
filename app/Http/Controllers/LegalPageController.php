<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function show(LegalPage $page): View
    {
        return view('pages.show', ['page' => $page]);
    }
}
