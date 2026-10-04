<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbEntry extends Model
{
    public const REVIEW_STATUSES = ['verified', 'needs_review', 'outdated'];

    /** نطاق الإجابة: عامة، أو خاصة بمسار/برنامج/تخصص بعينه (§G). */
    public const SCOPES = ['global', 'path', 'program', 'major'];

    protected $fillable = [
        'question', 'answer', 'keywords', 'is_custom',
        'scope_type', 'scope_id', 'source_id', 'version', 'last_verified_at',
        'review_status', 'category',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'is_custom' => 'boolean',
            'last_verified_at' => 'datetime',
        ];
    }

    /** مصدر البيانات الذي تستند إليه الإجابة. */
    public function source(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'source_id');
    }

    /** §85: الإجابات التي تحتاج مراجعة بعد تغيّر الشروط لا تُقدَّم كحقيقة. */
    public function needsReview(): bool
    {
        return $this->review_status !== 'verified';
    }
}
