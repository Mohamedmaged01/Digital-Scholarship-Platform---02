<?php

namespace App\Models;

use App\Models\Concerns\HasEnglishContent;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasEnglishContent;

    protected $fillable = ['code', 'title', 'description', 'detail', 'icon', 'duration', 'points', 'sort'];

    protected function casts(): array
    {
        return [
            'points' => 'array',
            'sort' => 'integer',
        ];
    }
}
