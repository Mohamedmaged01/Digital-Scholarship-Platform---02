<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class University extends Model
{
    public const REGIONS = ['na', 'europe', 'asia', 'oceania'];

    public const STATUSES = ['verified', 'needs_verification'];

    /**
     * `rank` و`acceptance` خرجا من هذه القائمة عمدًا (§47): التصنيف العالمي
     * ونسب القبول خارج نطاق أهلية الابتعاث، ولا مصدر رسمي يربطهما بها.
     * العمودان باقيان في القاعدة حفاظًا على البيانات القديمة فقط.
     */
    protected $fillable = [
        'name_en', 'name_ar', 'city', 'country', 'region', 'fields',
        'country_id', 'website', 'institution_status', 'source_id', 'last_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'last_verified_at' => 'datetime',
        ];
    }

    /** §J: لا تُعرض المؤسسة كمرتبطة بمسار ما لم يثبت ذلك من مصدر رسمي. */
    public function isVerified(): bool
    {
        return $this->institution_status === 'verified';
    }

    /** الدولة من الجدول المرجعي، حين تكون مُسندة. */
    public function countryRef(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /** مصدر البيانات الموثّق لهذه المؤسسة. */
    public function source(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'source_id');
    }

    /** روابط هذه المؤسسة بالمسارات والتخصصات. */
    public function pathLinks(): HasMany
    {
        return $this->hasMany(PathMajorInstitution::class, 'institution_id');
    }

    /** @return array<string, mixed> */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'nameAr' => $this->name_ar,
            'nameEn' => $this->name_en,
            'city' => $this->city,
            'country' => $this->country,
            'region' => $this->region,
            'status' => $this->institution_status,
        ];
    }
}
