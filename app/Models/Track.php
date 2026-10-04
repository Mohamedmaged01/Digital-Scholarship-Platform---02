<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    public const ICONS = ['rocket', 'boxes', 'flask-conical', 'award', 'stethoscope', 'satellite'];

    /** الدرجات الست المعتمدة (§I) — تطابق جدول `academic_degrees`. */
    public const DEGREES = ['bachelor', 'master', 'phd', 'fellowship', 'diploma', 'professional_doctorate'];

    public const APPLICATION_STATUSES = ['open', 'not_started', 'closed', 'unavailable'];

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
        return config('kasp.application_statuses')[$this->application_status] ?? 'لم يبدأ التقديم';
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
            'name' => $this->name,
            'badge' => $this->badge,
            'applicationStatus' => $this->application_status,
            'applicationStatusLabel' => $this->applicationStatusLabel(),
            'ranking' => $this->ranking,
            'perks' => $this->perks,
        ];
    }
}
