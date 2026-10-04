<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مصادر البيانات الموثقة (§I). كل حقيقة معروضة يجب أن تُنسب إلى مصدر منها.
 */
class DataSource extends Model
{
    protected $table = 'data_sources';

    public $incrementing = false;

    protected $keyType = 'string';

    public const TYPES = ['official', 'website', 'guideline'];

    protected $fillable = [
        'id', 'name_ar', 'name_en', 'source_type', 'url', 'authority', 'publication_date',
        'version', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
        ];
    }
}
