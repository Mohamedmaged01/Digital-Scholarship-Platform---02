<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ManagedFile extends Model
{
    protected $fillable = ['name', 'path', 'mime', 'size', 'uploaded_by'];

    protected function casts(): array
    {
        return ['size' => 'integer'];
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /** image | pdf | doc | other */
    public function kind(): string
    {
        return match (true) {
            str_starts_with($this->mime, 'image/') => 'image',
            $this->mime === 'application/pdf' => 'pdf',
            str_contains($this->mime, 'word'), str_contains($this->mime, 'sheet'), str_contains($this->mime, 'text') => 'doc',
            default => 'other',
        };
    }
}
