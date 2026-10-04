<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * البرامج (واعد وغيره) تحت مسار.
 */
class Program extends Model
{
    protected $table = 'programs';

    public const APPLICATION_STATUSES = ['not_started', 'open', 'closed', 'archived'];

    protected $fillable = [
        'path_id', 'name_ar', 'name_en', 'company_ar', 'institution_id', 'country_id', 'degree_id',
        'major_id', 'program_type', 'duration', 'study_start_date', 'application_start',
        'application_end', 'application_status', 'description_ar', 'description_en', 'status',
        'source_id', 'version', 'last_verified_at', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'study_start_date' => 'date',
            'application_start' => 'date',
            'application_end' => 'date',
            'last_verified_at' => 'datetime',
            'sort' => 'integer',
        ];
    }

    /** المسار الذي ينتمي إليه هذا السجل — `path_id` هو `tracks.id`. */
    public function track(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Track::class, 'path_id');
    }

    /** المؤسسة التعليمية. */
    public function institution(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(University::class, 'institution_id');
    }

    /** مصدر البيانات الموثّق لهذا السجل. */
    public function source(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'source_id');
    }
}
