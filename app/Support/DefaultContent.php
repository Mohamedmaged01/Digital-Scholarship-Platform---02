<?php

namespace App\Support;

use App\Models\ContactMethod;
use App\Models\Field;
use App\Models\Guide;
use App\Models\KbEntry;
use App\Models\LegalPage;
use App\Models\Major;
use App\Models\MajorConstraint;
use App\Models\News;
use App\Models\PathDegree;
use App\Models\PathMajor;
use App\Models\PathMajorInstitution;
use App\Models\Program;
use App\Models\Station;
use App\Models\Track;
use App\Models\University;
use Illuminate\Support\Facades\DB;

/**
 * المحتوى الافتراضي للمنصّة (database/data/*.json).
 * يستخدمه مُعبّئ قاعدة البيانات وأزرار «استعادة الافتراضي» في لوحة التحكم.
 *
 * المسارات والجامعات تُحدَّث بمعرّفها النصي (slug) بدل الحذف وإعادة الإنشاء،
 * حتى لا تُمحى روابطها بالتخصصات والبرامج والأدلة بالحذف المتتالي.
 */
class DefaultContent
{
    /** أسماء الأيقونات القديمة ← أسماء Lucide */
    private const ICONS = [
        'flask' => 'flask-conical',
        'shield' => 'shield-check',
        'pen' => 'file-pen-line',
        'upload' => 'file-up',
        'plane' => 'plane-takeoff',
    ];

    private static function load(string $name): array
    {
        return json_decode(file_get_contents(database_path("data/{$name}.json")), true);
    }

    private static function icon(string $icon): string
    {
        return self::ICONS[$icon] ?? $icon;
    }

    /** @return array<string, int> slug => id */
    private static function trackIds(): array
    {
        return Track::pluck('id', 'slug')->all();
    }

    /** @return array<string, int> slug => id */
    private static function universityIds(): array
    {
        return University::whereNotNull('slug')->pluck('id', 'slug')->all();
    }

    public static function resetTracks(): void
    {
        DB::transaction(function () {
            $slugs = [];
            foreach (self::load('tracks') as $i => $t) {
                $slugs[] = $t['id'];
                Track::updateOrCreate(['slug' => $t['id']], [
                    'code' => $t['code'],
                    'name' => $t['name'],
                    'en_subtitle' => $t['enSubtitle'],
                    'badge' => $t['badge'],
                    'description' => $t['desc'],
                    'icon' => self::icon($t['icon']),
                    'color' => $t['color'],
                    'degrees' => $t['degrees'],
                    'ranking' => $t['ranking'],
                    'fields' => $t['fields'],
                    'extra_fields' => $t['extraFields'],
                    'perks' => $t['perks'],
                    'image' => $t['image'],
                    'sort' => $i + 1,
                    'application_status' => $t['applicationStatus'] ?? 'not_started',
                    'application_start' => $t['applicationStart'] ?? null,
                    'application_end' => $t['applicationEnd'] ?? null,
                    'overview_ar' => $t['overviewAr'] ?? '',
                    'general_conditions' => $t['generalConditions'] ?? [],
                    'special_conditions' => $t['specialConditions'] ?? [],
                    'policies' => $t['policies'] ?? [],
                    'differentiation_criteria' => $t['differentiationCriteria'] ?? [],
                    'content_status' => $t['contentStatus'] ?? 'published',
                    'version' => $t['version'] ?? '2026-2027',
                ]);
            }
            Track::whereNotIn('slug', $slugs)->delete();
        });
    }

    public static function resetUniversities(): void
    {
        DB::transaction(function () {
            $slugs = [];
            foreach (self::load('universities') as $u) {
                $slugs[] = $u['id'];
                University::updateOrCreate(['slug' => $u['id']], [
                    'name_en' => $u['nameEn'],
                    'name_ar' => $u['nameAr'],
                    'city' => $u['city'],
                    'country' => $u['country'],
                    'region' => $u['region'],
                    'fields' => $u['fields'],
                    'website' => $u['website'] ?? '',
                    'institution_status' => $u['status'] ?? 'needs_verification',
                ]);
            }
            University::where(fn ($q) => $q->whereNull('slug')->orWhereNotIn('slug', $slugs))->delete();
        });
    }

    /**
     * المجالات والتخصصات وروابط: مسار ← درجة ← تخصص ← جامعة.
     * تُستبدل بالكامل بالقائمة الافتراضية (مستخدمة في المُعبّئ وزر الاستعادة في «إدارة العلاقات»).
     */
    public static function resetRelations(): void
    {
        $data = self::load('relations');
        $tracks = self::trackIds();
        $unis = self::universityIds();

        DB::transaction(function () use ($data, $tracks, $unis) {
            PathMajorInstitution::query()->delete();
            PathMajor::query()->delete();
            PathDegree::query()->delete();

            foreach ($data['fields'] as $i => $f) {
                Field::updateOrCreate(['id' => $f['id']], ['name_ar' => $f['name_ar'], 'name_en' => $f['name_en'], 'sort' => $i + 1]);
            }
            foreach ($data['majors'] as $i => $m) {
                Major::updateOrCreate(['id' => $m['id']], [
                    'name_ar' => $m['name_ar'], 'name_en' => $m['name_en'], 'field_id' => $m['field_id'], 'sort' => $i + 1,
                ]);
            }
            foreach ($data['pathDegrees'] as $r) {
                if (isset($tracks[$r['path_id']])) {
                    PathDegree::create(['path_id' => $tracks[$r['path_id']], 'degree_id' => $r['degree_id']]);
                }
            }
            foreach ($data['pathMajors'] as $r) {
                if (isset($tracks[$r['path_id']])) {
                    PathMajor::create(['path_id' => $tracks[$r['path_id']], 'major_id' => $r['major_id'], 'degree_id' => $r['degree_id']]);
                }
            }
            // §J: الروابط الافتراضية لم يُتحقق منها من مصدر رسمي بعد
            foreach ($data['pmi'] as $r) {
                if (isset($tracks[$r['path_id']], $unis[$r['institution_id']])) {
                    PathMajorInstitution::create([
                        'path_id' => $tracks[$r['path_id']],
                        'degree_id' => $r['degree_id'],
                        'major_id' => $r['major_id'],
                        'institution_id' => $unis[$r['institution_id']],
                        'status' => $r['status'] ?? 'needs_verification',
                    ]);
                }
            }
        });
    }

    public static function resetConstraints(): void
    {
        $tracks = self::trackIds();

        DB::transaction(function () use ($tracks) {
            MajorConstraint::query()->delete();
            foreach (self::load('constraints') as $c) {
                if (! isset($tracks[$c['pathId']])) {
                    continue;
                }
                MajorConstraint::create([
                    'path_id' => $tracks[$c['pathId']],
                    'major_id' => $c['majorId'],
                    'constraint_type' => $c['constraintType'],
                    'severity' => $c['severity'],
                    'title_ar' => $c['titleAr'],
                    'title_en' => $c['titleEn'] ?? '',
                    'description_ar' => $c['descriptionAr'] ?? '',
                    'allowed_degrees' => $c['allowedDegrees'] ?? null,
                    'excluded_degrees' => $c['excludedDegrees'] ?? null,
                    'value' => $c['value'] ?? '',
                    'source_ref' => $c['sourceRef'] ?? '',
                ]);
            }
        });
    }

    public static function resetWaedPrograms(): void
    {
        $waed = Track::where('slug', 'waed')->first();
        if (! $waed) {
            return;
        }

        DB::transaction(function () use ($waed) {
            Program::where('path_id', $waed->id)->delete();
            foreach (self::load('waed') as $i => $p) {
                Program::create([
                    'path_id' => $waed->id,
                    'slug' => $p['id'],
                    'name_ar' => $p['nameAr'],
                    'name_en' => $p['nameEn'],
                    'company_ar' => $p['company'],
                    'institution_name' => $p['institution'],
                    'country_name' => $p['country'],
                    'city' => $p['city'],
                    'program_type' => $p['programType'],
                    'duration' => $p['duration'],
                    'degree_id' => $p['degree'],
                    'sector' => $p['sector'],
                    'major_name' => $p['major'],
                    'required_majors' => $p['requiredMajors'],
                    'gpa' => $p['gpa'],
                    'languages' => $p['languages'],
                    'tests' => $p['tests'],
                    'conditions' => array_map(fn ($c) => [
                        'category' => $c['category'], 'text' => $c['text'], 'required' => (bool) $c['isRequired'],
                    ], $p['conditions']),
                    'application_start' => $p['applicationStart'],
                    'application_end' => $p['applicationEnd'],
                    'study_start_date' => $p['studyStart'],
                    'application_status' => $p['status'],
                    'description_ar' => $p['description'],
                    'website' => $p['website'],
                    'is_featured' => $p['isFeatured'],
                    'version' => $p['version'],
                    'notes' => $p['notes'],
                    'sort' => $i + 1,
                ]);
            }
        });
    }

    public static function resetGuides(): void
    {
        $tracks = self::trackIds();

        DB::transaction(function () use ($tracks) {
            Guide::query()->delete();
            foreach (self::load('guides') as $g) {
                Guide::create([
                    'path_id' => $tracks[$g['pathId']] ?? null,
                    'title_ar' => $g['titleAr'],
                    'title_en' => $g['titleEn'],
                    'guide_type' => $g['guideType'] === 'url' ? 'link' : $g['guideType'],
                    'url' => $g['url'],
                    'version' => $g['version'],
                    'publication_date' => $g['publicationDate'] ?: null,
                    'effective_date' => $g['effectiveDate'] ?: null,
                    'is_current' => $g['isCurrent'],
                    'status' => $g['status'],
                    'source_id' => $g['sourceId'] ?: null,
                    'notes' => $g['notes'],
                ]);
            }
        });
    }

    public static function resetPages(): void
    {
        DB::transaction(function () {
            foreach (self::load('pages') as $i => $p) {
                LegalPage::updateOrCreate(['slug' => $p['slug']], [
                    'title_ar' => $p['titleAr'], 'content' => $p['content'], 'sort' => $i + 1,
                ]);
            }
        });
    }

    public static function resetContactMethods(): void
    {
        DB::transaction(function () {
            ContactMethod::query()->delete();
            foreach (config('kasp.contact_methods') as $i => $c) {
                ContactMethod::create([
                    'label' => $c['label'], 'value' => $c['value'], 'href' => $c['href'], 'icon' => $c['icon'], 'sort' => $i + 1,
                ]);
            }
        });
    }

    public static function resetStations(): void
    {
        DB::transaction(function () {
            Station::query()->delete();
            foreach (self::load('stations') as $i => $s) {
                Station::create([
                    'code' => $s['n'],
                    'title' => $s['title'],
                    'description' => $s['desc'],
                    'detail' => $s['detail'],
                    'icon' => self::icon($s['icon']),
                    'duration' => $s['duration'],
                    'points' => $s['points'],
                    'sort' => $i + 1,
                ]);
            }
        });
    }

    public static function resetNews(): void
    {
        DB::transaction(function () {
            News::query()->delete();
            foreach (self::load('news') as $n) {
                News::create([
                    'title' => $n['title'],
                    'excerpt' => $n['excerpt'],
                    'body' => $n['body'],
                    'category' => $n['category'],
                    'pinned' => $n['pinned'],
                    'published_on' => $n['date'],
                    'author' => $n['author'],
                    'read_minutes' => $n['readMinutes'],
                    'image' => $n['image'] ?? '',
                ]);
            }
        });
    }

    /** الإجابات الأساسية فقط — لا تمس الإجابات المضافة من الإدارة */
    public static function resetCoreKnowledge(): void
    {
        DB::transaction(function () {
            KbEntry::where('is_custom', false)->delete();
            foreach (self::load('kb') as $k) {
                KbEntry::create([
                    'question' => $k['question'],
                    'answer' => $k['answer'],
                    'keywords' => $k['keywords'],
                    'is_custom' => false,
                ]);
            }
        });
    }
}
