<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** رقم يُعرض في شريط الإحصاءات أو الواجهة — يجب أن يستند إلى مصدر موثّق. */
class Statistic extends Model
{
    public const LOCATIONS = ['general', 'hero'];

    protected $fillable = ['label_ar', 'label_en', 'value', 'suffix', 'note_ar', 'note_en', 'location', 'source_id', 'sort', 'last_verified_at'];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'sort' => 'integer',
            'last_verified_at' => 'datetime',
        ];
    }
}
