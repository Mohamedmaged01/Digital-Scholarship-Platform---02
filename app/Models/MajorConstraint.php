<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** قيد أو استثناء خاص بتخصص داخل مسار، مصدره الدليل الاسترشادي. */
class MajorConstraint extends Model
{
    protected $fillable = [
        'path_id', 'major_id', 'constraint_type', 'severity', 'title_ar', 'title_en', 'description_ar',
        'allowed_degrees', 'excluded_degrees', 'value', 'source_ref',
    ];

    protected function casts(): array
    {
        return [
            'allowed_degrees' => 'array',
            'excluded_degrees' => 'array',
        ];
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'path_id');
    }
}
