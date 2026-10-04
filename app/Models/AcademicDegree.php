<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * الدرجات العلمية الست المعتمدة (§I).
 */
class AcademicDegree extends Model
{
    protected $table = 'academic_degrees';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['id', 'name_ar', 'name_en', 'sort', 'status'];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }
}
