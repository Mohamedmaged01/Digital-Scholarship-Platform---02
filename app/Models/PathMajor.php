<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ربط المسار بالتخصصات، مع تاريخ سريان لكل ربط.
 */
class PathMajor extends Model
{
    protected $table = 'path_majors';

    protected $fillable = [
        'path_id', 'major_id', 'degree_id', 'status', 'effective_from', 'effective_to', 'source_id',
        'version', 'last_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'last_verified_at' => 'datetime',
        ];
    }

    /** المسار الذي ينتمي إليه هذا السجل — `path_id` هو `tracks.id`. */
    public function track(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Track::class, 'path_id');
    }

    /** مصدر البيانات الموثّق لهذا السجل. */
    public function source(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'source_id');
    }
}
