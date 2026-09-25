<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $fillable = ['code', 'title', 'description', 'detail', 'icon', 'duration', 'points', 'sort'];

    protected function casts(): array
    {
        return [
            'points' => 'array',
            'sort' => 'integer',
        ];
    }
}
