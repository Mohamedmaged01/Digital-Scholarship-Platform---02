<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ربط المسار بالدرجات العلمية المتاحة فيه.
 */
class PathDegree extends Model
{
    protected $table = 'path_degrees';

    public const UPDATED_AT = null;

    protected $fillable = ['path_id', 'degree_id', 'status', 'source_id', 'version', 'last_verified_at'];

    protected function casts(): array
    {
        return [
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
