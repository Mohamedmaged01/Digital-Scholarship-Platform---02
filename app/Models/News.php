<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    public const CATEGORIES = ['announcement', 'admissions', 'meetings', 'partnerships'];

    protected $fillable = [
        'title', 'excerpt', 'body', 'category', 'pinned', 'published_on', 'author', 'read_minutes',
    ];

    protected function casts(): array
    {
        return [
            'pinned' => 'boolean',
            'published_on' => 'date',
            'read_minutes' => 'integer',
        ];
    }

    /** مثبّت أولًا ثم الأحدث */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('pinned')->orderByDesc('published_on')->orderByDesc('id');
    }

    /** @return array<string, mixed> */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'category' => $this->category,
            'pinned' => $this->pinned,
            'date' => $this->published_on->format('Y-m-d'),
            'author' => $this->author,
            'readMinutes' => $this->read_minutes,
        ];
    }
}
