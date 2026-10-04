<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * الأدلة الاسترشادية: ملف PDF أو رابط، بإصدار وتاريخ سريان.
 */
class Guide extends Model
{
    protected $table = 'guides';

    public const TYPES = ['pdf', 'link', 'document'];

    public const STATUSES = ['active', 'archived', 'draft'];

    protected $fillable = [
        'path_id', 'title_ar', 'title_en', 'guide_type', 'url', 'file_id', 'version',
        'publication_date', 'effective_date', 'is_current', 'status', 'source_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
            'effective_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    /** المسار الذي ينتمي إليه هذا السجل — `path_id` هو `tracks.id`. */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'path_id');
    }

    /** مصدر البيانات الموثّق لهذا السجل. */
    public function source(): BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'source_id');
    }

    /** الملف المرفوع، حين يكون الدليل من نوع PDF. */
    public function file(): BelongsTo
    {
        return $this->belongsTo(ManagedFile::class, 'file_id');
    }
}
