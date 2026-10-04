<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * البرامج (واعد وغيره) تحت مسار.
 */
class Program extends Model
{
    protected $table = 'programs';

    /** حالات برامج واعد: الحالية (open/upcoming) والسابقة (closed/archived). */
    public const APPLICATION_STATUSES = ['open', 'upcoming', 'closed', 'archived'];

    public const TYPES = ['scholarship', 'coop', 'training', 'fellowship'];

    protected $fillable = [
        'path_id', 'name_ar', 'name_en', 'company_ar', 'institution_id', 'country_id', 'degree_id',
        'major_id', 'program_type', 'duration', 'study_start_date', 'application_start',
        'application_end', 'application_status', 'description_ar', 'description_en', 'status',
        'source_id', 'version', 'last_verified_at', 'sort', 'slug', 'institution_name', 'country_name',
        'city', 'sector', 'major_name', 'required_majors', 'gpa', 'languages', 'tests', 'conditions',
        'website', 'is_featured', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'study_start_date' => 'date',
            'application_start' => 'date',
            'application_end' => 'date',
            'last_verified_at' => 'datetime',
            'sort' => 'integer',
            'required_majors' => 'array',
            'gpa' => 'array',
            'languages' => 'array',
            'tests' => 'array',
            'conditions' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    /** المسار الذي ينتمي إليه هذا السجل — `path_id` هو `tracks.id`. */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'path_id');
    }

    /** المؤسسة التعليمية. */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(University::class, 'institution_id');
    }

    /** مصدر البيانات الموثّق لهذا السجل. */
    public function source(): BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'source_id');
    }

    public function isPast(): bool
    {
        return in_array($this->application_status, ['closed', 'archived'], true);
    }

    public function statusLabel(): string
    {
        return config("kasp.waed_statuses.{$this->application_status}.label", $this->application_status);
    }

    public function statusClass(): string
    {
        return config("kasp.waed_statuses.{$this->application_status}.class", '');
    }

    public function scopeWaed($query)
    {
        return $query->whereHas('track', fn ($q) => $q->where('slug', 'waed'));
    }
}
