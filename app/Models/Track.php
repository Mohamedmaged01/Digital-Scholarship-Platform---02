<?php

namespace App\Models;

use App\Models\Concerns\HasEnglishContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    use HasEnglishContent;

    public const ICONS = ['rocket', 'boxes', 'flask-conical', 'award', 'stethoscope', 'satellite'];

    /** الدرجات المعتمدة (§I) — تطابق جدول `academic_degrees`. */
    public const DEGREES = ['bachelor', 'master', 'phd', 'fellowship', 'subspecialty', 'diploma', 'professional_doctorate'];

    /** per_program: مسار يعمل بنظام البرامج (واعد) — الحالة تُحدد لكل برنامج. */
    public const APPLICATION_STATUSES = ['open', 'not_started', 'closed', 'unavailable', 'per_program'];

    /** main: المسارات الأساسية. linked: تُعرض برابط المسار والدليل فقط (التميز والصحي). */
    public const TIERS = ['main', 'linked'];

    /**
     * نطاق التخصصات: all = جميع التخصصات متاحة (الرواد)، listed = التخصصات
     * المربوطة في «إدارة العلاقات»، specified = تخصصات محددة وفق الدليل لم تُنشر قائمتها.
     */
    public const MAJORS_SCOPES = ['all', 'listed', 'specified'];

    public const CONTENT_STATUSES = ['draft', 'review', 'approved', 'published'];

    /**
     * `gpa` خرج من هذه القائمة عمدًا (§12-13): لا يُعرض معدل عام ثابت للمسار،
     * فالشروط تُدار سجلًّا سجلًّا عبر جدول `requirements`. العمود باقٍ في
     * القاعدة للبيانات التاريخية فقط.
     */
    protected $fillable = [
        'slug', 'code', 'name', 'en_subtitle', 'badge', 'description', 'icon', 'color',
        'degrees', 'ranking', 'fields', 'extra_fields', 'perks', 'image', 'sort',
        'application_status', 'application_start', 'application_end', 'overview_ar',
        'general_conditions', 'special_conditions', 'policies', 'differentiation_criteria',
        'content_status', 'source_id', 'academic_year_id', 'last_verified_at', 'version',
        'tier', 'majors_scope', 'match_fields', 'institutions_count', 'external_url', 'extra_link_label', 'extra_link_url',
        'additional_conditions', 'faqs', 'details',
    ];

    protected function casts(): array
    {
        return [
            'degrees' => 'array',
            'fields' => 'array',
            'perks' => 'array',
            'general_conditions' => 'array',
            'special_conditions' => 'array',
            'policies' => 'array',
            'differentiation_criteria' => 'array',
            'additional_conditions' => 'array',
            'faqs' => 'array',
            'details' => 'array',
            'guideline_lists' => 'array',
            'match_fields' => 'array',
            'institutions_count' => 'integer',
            'application_start' => 'date',
            'application_end' => 'date',
            'last_verified_at' => 'datetime',
            'extra_fields' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function isFeatured(): bool
    {
        return $this->slug === 'rowad';
    }

    /** النص العربي لحالة التقديم — ما حلّ محل "أدنى معدل مطلوب" في البطاقة. */
    public function applicationStatusLabel(): string
    {
        return __(config('kasp.application_statuses')[$this->application_status] ?? 'لم يبدأ التقديم');
    }

    public function isMain(): bool
    {
        return $this->tier !== 'linked';
    }

    /** صفحة المسار في المنصة — واعد يعمل بنظام البرامج فله صفحته الخاصة. */
    public function url(): string
    {
        return $this->slug === 'waed' ? route('waed.index') : route('tracks.show', $this);
    }

    /** وصف نطاق التخصصات بدل أرقام ثابتة (ملاحظات 3 و35 و39). */
    public function majorsLabel(?int $listedCount = null): string
    {
        return match ($this->majors_scope) {
            'all' => __('جميع التخصصات متاحة'),
            'listed' => $listedCount ? __(':count تخصص', ['count' => $listedCount]) : __('تخصصات محددة للمسار'),
            default => __('تخصصات محددة لسنة التقديم'),
        };
    }

    /** العدد المحدد للمؤسسات التعليمية وفق الدليل، أو نطاق التصنيف النصي. */
    public function institutionsLabel(): string
    {
        return $this->institutions_count
            ? __('أفضل :count مؤسسة تعليمية حسب قائمة البرنامج', ['count' => $this->institutions_count])
            : ($this->tr('ranking') ?: __('المؤسسات التعليمية المحددة للمسار'));
    }

    /** هل للمسار ضوابط منشورة (سياسات أو شروط)؟ */
    public function hasGuidelineContent(): bool
    {
        return (bool) ($this->policies || $this->general_conditions || $this->additional_conditions || $this->special_conditions);
    }

    /** مصدر البيانات الموثّق لهذا المسار. */
    public function source(): BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'source_id');
    }

    /** الدرجات العلمية المرتبطة بالمسار عبر جدول الربط. */
    public function pathDegrees(): HasMany
    {
        return $this->hasMany(PathDegree::class, 'path_id');
    }

    /** التخصصات المرتبطة بالمسار. */
    public function pathMajors(): HasMany
    {
        return $this->hasMany(PathMajor::class, 'path_id');
    }

    /** ربط التخصص بالمؤسسة ضمن هذا المسار. */
    public function institutionLinks(): HasMany
    {
        return $this->hasMany(PathMajorInstitution::class, 'path_id');
    }

    /** برامج هذا المسار (واعد وغيره). */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'path_id');
    }

    /** الأدلة الاسترشادية المرتبطة بالمسار. */
    public function guides(): HasMany
    {
        return $this->hasMany(Guide::class, 'path_id');
    }

    /** الشروط الخاصة بهذا المسار من جدول `requirements`. */
    public function requirements()
    {
        return Requirement::where('scope_type', 'path')
            ->where('scope_id', (string) $this->id)
            ->orderBy('sort');
    }

    /** @return array<string, mixed> */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->slug,
            'name' => $this->tr('name'),
            'badge' => $this->tr('badge'),
            'applicationStatus' => $this->application_status,
            'applicationStatusLabel' => $this->applicationStatusLabel(),
            'ranking' => $this->tr('ranking'),
            'perks' => $this->tr('perks'),
        ];
    }
}
