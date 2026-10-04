<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ربط المسار بالمجالات المعرفية.
 */
class PathField extends Model
{
    protected $table = 'path_fields';

    public const UPDATED_AT = null;

    protected $fillable = ['path_id', 'field_id', 'status', 'source_id'];

    /** المسار الذي ينتمي إليه هذا السجل — `path_id` هو `tracks.id`. */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'path_id');
    }

    /** مصدر البيانات الموثّق لهذا السجل. */
    public function source(): BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'source_id');
    }
}
