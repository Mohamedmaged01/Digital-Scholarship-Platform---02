<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * الشروط والمتطلبات بنموذج مرن: عامة أو لمسار أو لبرنامج أو لتخصص.
 *
 * هذا الجدول هو بديل `tracks.gpa` المهجور (§L): لا يُعرض معدل عام ثابت
 * للمسار، بل شرط موثّق بمصدر وإصدار وتاريخ سريان.
 */
class Requirement extends Model
{
    protected $table = 'requirements';

    public const SCOPES = ['global', 'path', 'program', 'major'];

    protected $fillable = [
        'scope_type', 'scope_id', 'requirement_type', 'title_ar', 'title_en', 'description_ar',
        'description_en', 'value', 'unit', 'operator', 'is_required', 'status', 'source_id',
        'version', 'effective_from', 'effective_to', 'last_verified_at', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'last_verified_at' => 'datetime',
            'sort' => 'integer',
        ];
    }

    /** مصدر البيانات الموثّق لهذا السجل. */
    public function source(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'source_id');
    }
}
