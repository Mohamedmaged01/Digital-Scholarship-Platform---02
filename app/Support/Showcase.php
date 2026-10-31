<?php

namespace App\Support;

use App\Models\Field;
use App\Models\GuidelineInstitution;
use App\Models\Major;
use App\Models\MajorConstraint;
use App\Models\PathDegree;
use App\Models\PathMajor;
use App\Models\PathMajorInstitution;
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
        $en = app()->getLocale() === 'en';
        if ($managed->isNotEmpty()) {
            return $managed->map(fn (Statistic $s) => [
                'value' => $s->value,
                'suffix' => $s->suffix,
                'label' => $en && $s->label_en ? $s->label_en : $s->label_ar,
                'note' => $en && $s->note_en ? $s->note_en : $s->note_ar,
            ])->all();
        }

        // ملاحظة 39: لا أرقام ثابتة في الكود — العدد المحدد لكل مسار من بياناته (institutions_count)
        $structural = [
            ['value' => Track::count(), 'suffix' => '', 'label' => __('مسارات ابتعاث'), 'note' => __('لكل مسار شروطه وضوابطه الخاصة')],
            ...Track::whereNotNull('institutions_count')->orderBy('sort')->get()->map(fn (Track $t) => [
                'value' => $t->institutions_count, 'suffix' => '', 'label' => __('مؤسسة تعليمية — :track', ['track' => $t->tr('name')]), 'note' => __('حسب قائمة البرنامج في المسار'),
            ])->all(),
        ];

        return array_slice($structural, 0, $location === 'hero' ? 3 : 4);
    }

    /**
     * شجرة المستكشف لمسار واحد: الدرجات ← التخصصات (مجمّعة بالمجال) ← الجامعات،
     * مع قيود كل تخصص. تُمرَّر إلى Alpine دفعة واحدة.
     */
    public static function explorer(Track $track): array
    {
        $degreeNames = array_map(fn ($n) => __($n), config('kasp.degrees'));
        $en = app()->getLocale() === 'en';
        $fields = Field::all()->mapWithKeys(fn ($f) => [$f->id => $en && $f->name_en ? $f->name_en : $f->name_ar]);
        $majors = Major::all()->keyBy('id');

        $pathMajors = PathMajor::where('path_id', $track->id)->get();
        $links = PathMajorInstitution::with('institution')->where('path_id', $track->id)->get();
        $constraints = MajorConstraint::where('path_id', $track->id)->get()->groupBy('major_id');

        $degrees = PathDegree::where('path_id', $track->id)->pluck('degree_id')
            ->sortBy(fn ($d) => array_search($d, array_keys($degreeNames)))
            ->values()
            ->map(function (string $degree) use ($degreeNames, $pathMajors, $majors, $fields, $links, $en) {
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
                            'name' => $en && $m->name_en ? $m->name_en : $m->name_ar,
                            'field' => $fields[$m->field_id] ?? __('أخرى'),
                            'universities' => $links->where('degree_id', $degree)->where('major_id', $m->id)
                                ->pluck('institution')->filter()->sortBy('name_ar')->values()
                                ->map(fn ($u) => [
                                    'id' => $u->id,
                                    'nameAr' => $en ? $u->name_en : $u->name_ar,
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
                'title' => $en && $c->title_en ? $c->title_en : $c->title_ar,
                'description' => $c->description_ar,
                'type' => __(config("kasp.constraint_types.{$c->constraint_type}", $c->constraint_type)),
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
     * قوائم الدليل الاسترشادي للمسار: لكل مجال تخصصاته ومؤسساته التعليمية بترتيب الدليل
     * (ملاحظتا 32 و33). المسار بلا قوائم يعيد مصفوفة فارغة.
     */
    public static function guideline(Track $track): array
    {
        if (empty($track->guideline_lists)) {
            return [];
        }

        $en = app()->getLocale() === 'en';
        $meta = DefaultContent::guidelineMeta();
        $institutions = GuidelineInstitution::with('institution')->where('path_id', $track->id)
            ->orderBy('position')->get()->groupBy('field_key');

        return collect($track->guideline_lists)->map(fn (array $list) => [
            'key' => $list['key'],
            'name' => $en ? $list['nameEn'] : $list['nameAr'],
            'majors' => array_map(fn (array $m) => [
                'name' => $en ? $m['en'] : $m['ar'],
                'alt' => $en ? $m['ar'] : $m['en'],
                'mastersOnly' => ($m['degree'] ?? null) === 'master',
            ], $list['majors']),
            'institutions' => ($institutions[$list['key']] ?? collect())->filter->institution->map(fn (GuidelineInstitution $g) => [
                'position' => $g->position,
                'name' => $g->institution->name_en,
                'country' => $en ? __($g->institution->country) : $g->institution->country,
                'note' => $g->note ? ($meta['notes'][$g->note][$en ? 'en' : 'ar'] ?? null) : null,
            ])->values()->all(),
        ])->all();
    }

    /**
     * بيانات محرّك التوجيه (ملاحظة 31): المطابقة مبنية على ضوابط كل مسار مباشرة —
     * الدرجات المرتبطة به، ونطاق تخصصاته، والتخصصات المربوطة فعليًا، وشروطه الخاصة.
     */
    public static function matcher(Collection $tracks): array
    {
        $degreeNames = array_map(fn ($n) => __($n), config('kasp.degrees'));
        $en = app()->getLocale() === 'en';
        $majors = Major::all()->keyBy('id');
        $pathDegrees = PathDegree::all()->groupBy('path_id');
        $pathMajors = PathMajor::all()->groupBy('path_id');
        $links = PathMajorInstitution::all()->groupBy('path_id');

        $usedDegrees = PathDegree::distinct()->pluck('degree_id')->all();

        return [
            'degrees' => collect($degreeNames)->only($usedDegrees)
                ->map(fn ($name, $id) => ['id' => $id, 'name' => $name, 'nameEn' => ucwords(str_replace('_', ' ', $id))])
                ->values()->all(),
            'fields' => Field::orderBy('sort')->get(['id', 'name_ar', 'name_en'])->map(fn ($f) => ['id' => $f->id, 'name' => $en && $f->name_en ? $f->name_en : $f->name_ar])->all(),
            'tracks' => $tracks->map(function (Track $t) use ($pathDegrees, $pathMajors, $links, $majors, $degreeNames, $en) {
                $trackLinks = $links[$t->id] ?? collect();
                $degrees = ($pathDegrees[$t->id] ?? collect())->pluck('degree_id')->values();

                return [
                    'id' => $t->slug,
                    'code' => $t->code,
                    'name' => $t->tr('name'),
                    'url' => $t->url(),
                    'status' => $t->application_status,
                    'statusLabel' => $t->applicationStatusLabel(),
                    'degrees' => $degrees->all(),
                    'degreeNames' => $degrees->map(fn ($d) => $degreeNames[$d] ?? $d)->all(),
                    'scope' => $t->majors_scope,
                    'scopeLabel' => $t->majorsLabel(),
                    'matchFields' => $t->match_fields ?? [],
                    'institutions' => $t->institutionsLabel(),
                    'rules' => array_slice(self::flattenItems($t->tr('special_conditions')), 0, 2),
                    'majors' => ($pathMajors[$t->id] ?? collect())->map(fn ($pm) => [
                        'degree' => $pm->degree_id,
                        'name' => $en ? ($majors->get($pm->major_id)?->name_en ?: $majors->get($pm->major_id)?->name_ar) : $majors->get($pm->major_id)?->name_ar,
                        'field' => $majors->get($pm->major_id)?->field_id,
                        'unis' => $trackLinks->where('degree_id', $pm->degree_id)->where('major_id', $pm->major_id)->count(),
                    ])->filter(fn ($m) => $m['name'])->values()->all(),
                ];
            })->values()->all(),
        ];
    }

    /** بنود الأقسام نصوصًا مسطّحة (البنود الفرعية تُلحق بنص البند). */
    public static function flattenItems(?array $sections): array
    {
        $out = [];
        foreach ($sections ?? [] as $s) {
            foreach ($s['items'] ?? [] as $item) {
                $out[] = is_array($item) ? trim($item['text'].' '.implode(' ', $item['sub'] ?? [])) : $item;
            }
        }

        return $out;
    }
}
