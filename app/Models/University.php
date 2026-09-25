<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    public const REGIONS = ['na', 'europe', 'asia', 'oceania'];

    protected $fillable = ['rank', 'name_en', 'name_ar', 'city', 'country', 'region', 'fields', 'acceptance'];

    protected function casts(): array
    {
        return [
            'rank' => 'integer',
            'fields' => 'array',
        ];
    }

    /** @return array<string, mixed> */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'rank' => $this->rank,
            'nameAr' => $this->name_ar,
            'region' => $this->region,
        ];
    }
}
