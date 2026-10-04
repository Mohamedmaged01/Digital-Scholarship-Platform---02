<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicDegree;
use App\Models\Field;
use App\Models\Major;
use App\Models\PathDegree;
use App\Models\PathMajor;
use App\Models\PathMajorInstitution;
use App\Models\Track;
use App\Models\University;
use App\Support\DefaultContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * إدارة العلاقات: المسار ← الدرجة ← التخصص ← الجامعة.
 * تُعرض مسارًا واحدًا في كل مرة لأن شبكة الجامعات قد تكبر.
 */
class RelationController extends Controller
{
    public function index(Request $request): View
    {
        $tracks = Track::where('slug', '!=', 'waed')->orderBy('sort')->get();
        $track = $tracks->firstWhere('slug', $request->query('track')) ?? $tracks->first();

        $pathMajors = $track ? PathMajor::where('path_id', $track->id)->get() : collect();

        return view('admin.relations', [
            'tracks' => $tracks,
            'track' => $track,
            'degrees' => AcademicDegree::orderBy('sort')->get(),
            'fields' => Field::orderBy('sort')->get(),
            'majors' => Major::orderBy('sort')->get(),
            'universities' => University::orderBy('name_ar')->get(['id', 'name_ar', 'name_en']),
            'linkedDegrees' => $track ? PathDegree::where('path_id', $track->id)->pluck('degree_id')->all() : [],
            'pathMajors' => $pathMajors,
            'links' => $track ? PathMajorInstitution::where('path_id', $track->id)->get()
                ->groupBy(fn ($l) => "{$l->degree_id}|{$l->major_id}")
                ->map(fn ($g) => $g->pluck('institution_id')->all()) : collect(),
            'statuses' => $track ? PathMajorInstitution::where('path_id', $track->id)
                ->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status') : collect(),
        ]);
    }

    /** ربط المسار بالدرجات والتخصصات (لكل درجة) دفعة واحدة. */
    public function updateMajors(Request $request, Track $track): RedirectResponse
    {
        $data = $request->validate([
            'degrees' => ['nullable', 'array'],
            'degrees.*' => [Rule::exists('academic_degrees', 'id')],
            'majors' => ['nullable', 'array'],
            'majors.*' => ['array'],
            'majors.*.*' => [Rule::exists('majors', 'id')],
        ]);

        $degrees = $data['degrees'] ?? [];

        DB::transaction(function () use ($track, $degrees, $data) {
            PathDegree::where('path_id', $track->id)->whereNotIn('degree_id', $degrees)->delete();
            foreach ($degrees as $d) {
                PathDegree::firstOrCreate(['path_id' => $track->id, 'degree_id' => $d]);
            }

            $wanted = collect($data['majors'] ?? [])
                ->only($degrees)
                ->flatMap(fn ($ids, $degree) => collect($ids)->map(fn ($id) => "{$degree}|{$id}"));

            foreach (PathMajor::where('path_id', $track->id)->get() as $pm) {
                if (! $wanted->contains("{$pm->degree_id}|{$pm->major_id}")) {
                    // إزالة التخصص تزيل روابط جامعاته في هذه الدرجة
                    PathMajorInstitution::where(['path_id' => $track->id, 'degree_id' => $pm->degree_id, 'major_id' => $pm->major_id])->delete();
                    $pm->delete();
                }
            }
            foreach ($wanted as $key) {
                [$degree, $major] = explode('|', $key, 2);
                PathMajor::firstOrCreate(['path_id' => $track->id, 'degree_id' => $degree, 'major_id' => $major]);
            }
            PathMajorInstitution::where('path_id', $track->id)->whereNotIn('degree_id', $degrees)->delete();
        });

        return redirect()->route('admin.relations.index', ['track' => $track->slug])->with('status', 'تم حفظ الدرجات والتخصصات');
    }

    /** الجامعات المرتبطة بكل (درجة، تخصص) في المسار. */
    public function updateInstitutions(Request $request, Track $track): RedirectResponse
    {
        $data = $request->validate([
            'links' => ['nullable', 'array'],
            'links.*' => ['array'],
            'links.*.*' => [Rule::exists('universities', 'id')],
            'status' => ['required', Rule::in(PathMajorInstitution::STATUSES)],
        ]);

        $valid = PathMajor::where('path_id', $track->id)->get()->map(fn ($pm) => "{$pm->degree_id}|{$pm->major_id}");

        DB::transaction(function () use ($track, $data, $valid) {
            $existing = PathMajorInstitution::where('path_id', $track->id)->get();
            $wanted = collect($data['links'] ?? [])->only($valid->all())
                ->flatMap(fn ($ids, $key) => collect($ids)->map(fn ($id) => "{$key}|{$id}"));

            foreach ($existing as $l) {
                if (! $wanted->contains("{$l->degree_id}|{$l->major_id}|{$l->institution_id}")) {
                    $l->delete();
                }
            }
            foreach ($wanted as $key) {
                [$degree, $major, $uni] = explode('|', $key, 3);
                $link = PathMajorInstitution::firstOrNew([
                    'path_id' => $track->id, 'degree_id' => $degree, 'major_id' => $major, 'institution_id' => (int) $uni,
                ]);
                if (! $link->exists) {
                    $link->status = $data['status'];
                    $link->save();
                }
            }
        });

        return redirect()->route('admin.relations.index', ['track' => $track->slug])->with('status', 'تم حفظ روابط الجامعات');
    }

    /** اعتماد كل روابط المسار كموثّقة أو إعادتها لـ«بحاجة إلى تحقق». */
    public function verify(Request $request, Track $track): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(PathMajorInstitution::STATUSES)]]);
        PathMajorInstitution::where('path_id', $track->id)->update([
            'status' => $data['status'],
            'last_verified_at' => $data['status'] === 'verified' ? now() : null,
        ]);

        return redirect()->route('admin.relations.index', ['track' => $track->slug])->with('status', 'تم تحديث حالة التحقق');
    }

    /** إضافة تخصص جديد إلى القائمة المرجعية. */
    public function storeMajor(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name_ar' => ['required', 'string', 'min:2', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'field_id' => ['required', 'exists:fields,id'],
        ]);

        $base = 'm-'.(Str::slug($data['name_en'] ?? '') ?: Str::lower(Str::random(6)));
        $id = $base;
        for ($i = 2; Major::whereKey($id)->exists(); $i++) {
            $id = "{$base}-{$i}";
        }

        Major::create($data + ['id' => $id, 'name_en' => $data['name_en'] ?? '', 'sort' => (int) Major::max('sort') + 1]);

        return back()->with('status', "تمت إضافة التخصص «{$data['name_ar']}»");
    }

    public function reset(): RedirectResponse
    {
        DefaultContent::resetRelations();
        DefaultContent::resetConstraints();

        return redirect()->route('admin.relations.index')->with('status', 'تمت استعادة العلاقات الافتراضية');
    }
}
