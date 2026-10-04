<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * السنوات الأكاديمية. الإصدار الحالي يحدَّد بـ `is_current`.
 */
class AcademicYear extends Model
{
    protected $table = 'academic_years';

    public $incrementing = false;

    protected $keyType = 'string';

    public const UPDATED_AT = null;

    protected $fillable = ['id', 'name_ar', 'name_en', 'start_date', 'end_date', 'is_current'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }
}
