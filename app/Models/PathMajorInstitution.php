<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ربط التخصص بالمؤسسة ضمن مسار — العلاقة الأدق في النموذج.
 *
 * §J: لا تُنشأ هذه السجلات بالتخمين. أي سجل بلا مصدر رسمي يبقى
 * `needs_verification` ولا يُعرض للمستخدم كحقيقة مؤكدة.
 */
class PathMajorInstitution extends Model
{
    protected $table = 'path_major_institutions';

    public const STATUSES = ['verified', 'needs_verification', 'archived'];

    protected $fillable = [
        'path_id', 'degree_id', 'field_id', 'major_id', 'institution_id', 'country_id', 'status',
        'source_id', 'version', 'effective_from', 'effective_to', 'last_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'last_verified_at' => 'datetime',
        ];
    }

    /** المسار الذي ينتمي إليه هذا السجل — `path_id` هو `tracks.id`. */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'path_id');
    }

    /** المؤسسة التعليمية. */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(University::class, 'institution_id');
    }

    /** مصدر البيانات الموثّق لهذا السجل. */
    public function source(): BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'source_id');
    }

    /** السجلات الموثقة فقط — ما يصحّ عرضه للزائر. */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }
}
