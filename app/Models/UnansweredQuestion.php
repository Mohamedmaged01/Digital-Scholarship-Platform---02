<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnansweredQuestion extends Model
{
    public $timestamps = false;

    protected $fillable = ['question', 'asked_count', 'first_asked_at', 'last_asked_at'];

    protected function casts(): array
    {
        return [
            'asked_count' => 'integer',
            'first_asked_at' => 'datetime',
            'last_asked_at' => 'datetime',
        ];
    }
}
