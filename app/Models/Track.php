<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    public const ICONS = ['rocket', 'boxes', 'flask-conical', 'award', 'stethoscope', 'satellite'];

    public const DEGREES = ['bachelor', 'master', 'phd'];

    protected $fillable = [
        'slug', 'code', 'name', 'en_subtitle', 'badge', 'description', 'icon', 'color',
        'degrees', 'gpa', 'ranking', 'fields', 'extra_fields', 'perks', 'image', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'degrees' => 'array',
            'fields' => 'array',
            'perks' => 'array',
            'extra_fields' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function isFeatured(): bool
    {
        return $this->slug === 'rowad';
    }

    /** @return array<string, mixed> */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->slug,
            'name' => $this->name,
            'badge' => $this->badge,
            'gpa' => $this->gpa,
            'ranking' => $this->ranking,
            'perks' => $this->perks,
        ];
    }
}
