<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * الدول مع المنطقة الجغرافية.
 */
class Country extends Model
{
    protected $table = 'countries';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public const REGIONS = ['na', 'europe', 'asia', 'oceania', 'other'];

    protected $fillable = ['id', 'name_ar', 'name_en', 'region', 'iso_code', 'sort', 'status'];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }
}
