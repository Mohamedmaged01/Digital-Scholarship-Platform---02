<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ملاحظات الوكالة (نوفمبر 2026):
 * - مسارا التميز والصحي يظهران ببطاقات كاملة مثل بقية المسارات بدل قسم
 *   «مسارات أخرى — رابط المسار والدليل». يبقى خيار «طريقة العرض» متاحًا للمحرّر.
 * - الاسم الإنجليزي الرسمي للبرنامج كما في شعاره في النصوص الإنجليزية للمسارات.
 */
return new class extends Migration
{
    private const OLD_NAME = 'King Salman Scholarship Program';

    private const NAME = 'Custodian of the Two Holy Mosques Scholarship Program';

    public function up(): void
    {
        DB::table('tracks')->whereIn('slug', ['tamayoz', 'sahhi'])->where('tier', 'linked')->update(['tier' => 'main']);

        DB::table('tracks')->where('content_en', 'like', '%'.self::OLD_NAME.'%')
            ->update(['content_en' => DB::raw('REPLACE(content_en, '.DB::getPdo()->quote(self::OLD_NAME).', '.DB::getPdo()->quote(self::NAME).')')]);
    }

    public function down(): void
    {
        DB::table('tracks')->whereIn('slug', ['tamayoz', 'sahhi'])->where('tier', 'main')->update(['tier' => 'linked']);
    }
};
