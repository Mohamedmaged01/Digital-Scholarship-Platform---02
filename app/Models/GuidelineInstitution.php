<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** مؤسسة تعليمية في قائمة أحد مجالات المسار بترتيبها في الدليل الاسترشادي. */
class GuidelineInstitution extends Model
{
    public $timestamps = false;

    protected $fillable = ['path_id', 'field_key', 'institution_id', 'position', 'note'];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(University::class, 'institution_id');
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'path_id');
    }
}
