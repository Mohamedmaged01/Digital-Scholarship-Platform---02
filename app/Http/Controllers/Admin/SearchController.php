<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KbEntry;
use App\Models\ManagedFile;
use App\Models\News;
use App\Models\Station;
use App\Models\Track;
use App\Models\University;
use App\Support\Lists;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** البحث العام في محتوى لوحة التحكم */
class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = mb_substr((string) $request->query('q', ''), 0, 120);
        $sections = config('kasp.admin_sections');
        $hits = [];
        $hit = function (string $route, string $url, string $title, string $sub = '') use (&$hits, $sections) {
            $hits[] = [
                'section' => $sections[$route]['label'],
                'icon' => svg('lucide-'.$sections[$route]['icon'], 'size-4')->toHtml(),
                'url' => $url,
                'title' => $title,
                'sub' => $sub,
            ];
        };

        foreach (News::ordered()->get() as $n) {
            if (Lists::matches($q, "{$n->title} {$n->excerpt} {$n->author}")) {
                $hit('admin.news.index', route('admin.news.index', ['edit' => $n->id]), $n->title, $n->published_on->format('Y-m-d'));
            }
        }
        foreach (KbEntry::all() as $k) {
            if (Lists::matches($q, "{$k->question} {$k->answer} ".implode(' ', $k->keywords))) {
                $params = $k->is_custom ? ['edit' => $k->id] : ['q' => $k->question];
                $hit('admin.kb.index', route('admin.kb.index', $params), $k->question, $k->is_custom ? 'مضافة من الإدارة' : 'أساسية');
            }
        }
        foreach (University::orderBy('rank')->get() as $u) {
            if (Lists::matches($q, "{$u->name_ar} {$u->name_en} {$u->country} {$u->city} ".implode(' ', $u->fields))) {
                $hit('admin.universities.index', route('admin.universities.index', ['edit' => $u->id]), $u->name_ar, $u->name_en);
            }
        }
        foreach (Track::orderBy('sort')->get() as $t) {
            if (Lists::matches($q, "{$t->name} {$t->en_subtitle} ".implode(' ', $t->fields))) {
                $hit('admin.tracks.index', route('admin.tracks.index', ['edit' => $t->id]), $t->name, $t->badge);
            }
        }
        foreach (Station::orderBy('sort')->get() as $s) {
            if (Lists::matches($q, "{$s->title} {$s->description} ".implode(' ', $s->points))) {
                $hit('admin.stations.index', route('admin.stations.index', ['edit' => $s->id]), "المحطة {$s->code}: {$s->title}", $s->duration);
            }
        }
        foreach (ManagedFile::latest()->get() as $f) {
            if (Lists::matches($q, $f->name)) {
                $hit('admin.files.index', route('admin.files.index', ['highlight' => $f->id]), $f->name, round($f->size / 1024).' KB');
            }
        }

        return response()->json(['hits' => array_slice($hits, 0, 12)]);
    }
}
