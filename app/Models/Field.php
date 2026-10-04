<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * المجالات المعرفية.
 *
 * تنبيه: لا علاقة لهذا بعمود JSON المسمّى `fields` في `tracks` و`universities`؛
 * ذاك نصوص حرة قديمة، وهذا الجدول المرجعي.
 */
class Field extends Model
{
    protected $table = 'fields';

    public $incrementing = false;

    protected $keyType = 'string';

    public const UPDATED_AT = null;

    protected $fillable = ['id', 'name_ar', 'name_en', 'sort', 'status'];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    /** التخصصات تحت هذا المجال. */
    public function majors(): HasMany
    {
        return $this->hasMany(Major::class, 'field_id');
    }
}
