<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbEntry extends Model
{
    protected $fillable = ['question', 'answer', 'keywords', 'is_custom'];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'is_custom' => 'boolean',
        ];
    }
}
