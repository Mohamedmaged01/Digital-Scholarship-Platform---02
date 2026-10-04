<?php

namespace App\Support;

use App\Models\AcademicDegree;
use App\Models\DataSource;
use App\Models\Field;
use App\Models\Major;
use App\Models\MajorConstraint;
use App\Models\PathDegree;
use App\Models\PathMajor;
use App\Models\PathMajorInstitution;
use App\Models\Station;
use App\Models\Statistic;
use App\Models\Track;
use Illuminate\Support\Collection;

/**
 * البيانات المشتقّة التي تعرضها الواجهة العامة: الأرقام، ومستكشف المسار،
 * وبيانات محرّك التوجيه. كلها تُبنى من الجداول العلائقية لا من قيم ثابتة.
 */
class Showcase
{
    /**
     * أرقام موقع معيّن (general | hero). إن لم تُضف الإدارة أرقامًا موثّقة،
     * تُعرض الأرقام الهيكلية التي يحسبها النظام من بنيته (§K).
     *
     * @return list<array{value: int, suffix: string, label: string, note: string}>
     */
    public static function statistics(string $location): array
    {
        $managed = Statistic::where('location', $location)->orderBy('sort')->get();
        if ($managed->isNotEmpty()) {
            return $managed->map(fn (Statistic $s) => [
                'value' => $s->value, 'suffix' => $s->suffix, 'label' => $s->label_ar, 'note' => $s->note_ar,
            ])->all();
        }

        $structural = [
            ['value' => Track::count(), 'suffix' => '', 'label' => 'مسارات ابتعاث', 'note' => 'لكل مسار شروطه وتخصصاته'],
            ['value' => Station::count(), 'suffix' => '', 'label' => 'محطات في خارطة الطريق', 'note' => 'من التقديم إلى ما بعد التخرج'],
            ['value' => AcademicDegree::where('status', 'active')->count(), 'suffix' => '', 'label' => 'درجات علمية معتمدة', 'note' => 'من الدبلوم إلى الدكتوراه المهنية'],
            ['value' => DataSource::where('status', 'active')->count(), 'suffix' => '', 'label' => 'مصادر بيانات معتمدة', 'note' => 'كل معلومة تُنسب إلى مصدرها'],
        ];

        return $location === 'hero' ? array_slice($structural, 0, 3) : $structural;
    }

    /**
     * شجرة المستكشف لمسار واحد: الدرجات ← التخصصات (مجمّعة بالمجال) ← الجامعات،
     * مع قيود كل تخصص. تُمرَّر إلى Alpine دفعة واحدة.
     */
    public static function explorer(Track $track): array
    {
        $degreeNames = config('kasp.degrees');
        $fields = Field::pluck('name_ar', 'id');
        $majors = Major::all()->keyBy('id');

        $pathMajors = PathMajor::where('path_id', $track->id)->get();
        $links = PathMajorInstitution::with('institution')->where('path_id', $track->id)->get();
        $constraints = MajorConstraint::where('path_id', $track->id)->get()->groupBy('major_id');

        $degrees = PathDegree::where('path_id', $track->id)->pluck('degree_id')
            ->sortBy(fn ($d) => array_search($d, array_keys($degreeNames)))
            ->values()
            ->map(function (string $degree) use ($degreeNames, $pathMajors, $majors, $fields, $links) {
                return [
                    'id' => $degree,
                    'name' => $degreeNames[$degree] ?? $degree,
                    'majors' => $pathMajors->where('degree_id', $degree)
                        ->map(fn ($pm) => $majors->get($pm->major_id))
                        ->filter()
                        ->sortBy('sort')
                        ->values()
                        ->map(fn (Major $m) => [
                            'id' => $m->id,
                            'name' => $m->name_ar,
                            'field' => $fields[$m->field_id] ?? 'أخرى',
                            'universities' => $links->where('degree_id', $degree)->where('major_id', $m->id)
                                ->pluck('institution')->filter()->sortBy('name_ar')->values()
                                ->map(fn ($u) => [
                                    'id' => $u->id,
                                    'nameAr' => $u->name_ar,
                                    'nameEn' => $u->name_en,
                                    'city' => $u->city,
                                    'country' => $u->country,
                                    'website' => $u->website,
                                    'verified' => $u->isVerified(),
                                ])->all(),
                        ])->all(),
                ];
            })->all();

        return [
            'degrees' => $degrees,
            'constraints' => $constraints->map(fn (Collection $items) => $items->map(fn (MajorConstraint $c) => [
                'id' => $c->id,
                'title' => $c->title_ar,
                'description' => $c->description_ar,
                'type' => config("kasp.constraint_types.{$c->constraint_type}", $c->constraint_type),
                'class' => config("kasp.constraint_severities.{$c->severity}.class", ''),
                'source' => $c->source_ref,
                'allowed' => array_map(fn ($d) => $degreeNames[$d] ?? $d, $c->allowed_degrees ?? []),
                'allowedIds' => $c->constraint_type === 'degree_restriction' ? ($c->allowed_degrees ?? []) : [],
            ])->values())->all(),
            'totals' => [
                'majors' => $pathMajors->pluck('major_id')->unique()->count(),
                'universities' => $links->pluck('institution_id')->unique()->count(),
            ],
        ];
    }

    /**
     * بيانات محرّك التوجيه: الدرجات والمجالات، ولكل مسار (عدا واعد الذي يعمل
     * بنظام البرامج) درجاته وتخصصاته وروابط الجامعات.
     */
    public static function matcher(Collection $tracks): array
    {
        $degreeNames = config('kasp.degrees');
        $majors = Major::all()->keyBy('id');
        $pathDegrees = PathDegree::all()->groupBy('path_id');
        $pathMajors = PathMajor::all()->groupBy('path_id');
        $links = PathMajorInstitution::all()->groupBy('path_id');

        $usedDegrees = PathDegree::distinct()->pluck('degree_id')->all();

        return [
            'degrees' => collect($degreeNames)->only($usedDegrees)
                ->map(fn ($name, $id) => ['id' => $id, 'name' => $name, 'nameEn' => ucwords(str_replace('_', ' ', $id))])
                ->values()->all(),
            'fields' => Field::orderBy('sort')->get(['id', 'name_ar'])->map(fn ($f) => ['id' => $f->id, 'name' => $f->name_ar])->all(),
            'tracks' => $tracks->reject(fn (Track $t) => $t->slug === 'waed')->map(fn (Track $t) => [
                'id' => $t->slug,
                'code' => $t->code,
                'name' => $t->name,
                'badge' => $t->badge,
                'url' => route('tracks.show', $t),
                'degrees' => ($pathDegrees[$t->id] ?? collect())->pluck('degree_id')->values()->all(),
                'degreeNames' => ($pathDegrees[$t->id] ?? collect())->pluck('degree_id')->map(fn ($d) => $degreeNames[$d] ?? $d)->values()->all(),
                'majors' => ($pathMajors[$t->id] ?? collect())->map(fn ($pm) => [
                    'id' => $pm->major_id,
                    'degree' => $pm->degree_id,
                    'name' => $majors->get($pm->major_id)?->name_ar,
                    'field' => $majors->get($pm->major_id)?->field_id,
                ])->filter(fn ($m) => $m['name'])->values()->all(),
                'links' => ($links[$t->id] ?? collect())->map(fn ($l) => [
                    'degree' => $l->degree_id, 'major' => $l->major_id, 'uni' => $l->institution_id,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }
}
