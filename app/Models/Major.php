<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * التخصصات، كل تخصص تحت مجال معرفي.
 */
class Major extends Model
{
    protected $table = 'majors';

    public $incrementing = false;

    protected $keyType = 'string';

    public const UPDATED_AT = null;

    protected $fillable = ['id', 'name_ar', 'name_en', 'field_id', 'sort', 'status'];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    /** المجال المعرفي الذي ينتمي إليه التخصص. */
    public function field(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Field::class, 'field_id');
    }
}
