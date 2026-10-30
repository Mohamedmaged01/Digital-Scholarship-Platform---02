<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * روابط الدليل الاسترشادي الرسمي وصفحات المسارات في موقع الوزارة
 * (ملاحظات الوكالة — أكتوبر 2026). تُحدَّث القيم الافتراضية القديمة فقط،
 * فلا يُمسّ ما عدّله المحرّر من لوحة التحكم، ولا حاجة لإعادة تشغيل db:seed.
 */
return new class extends Migration
{
    private const OLD_GUIDE_URL = 'https://www.moe.gov.sa/ar/pages/scholarship.aspx';

    private const GUIDE_URL = 'https://object.moe.gov.sa/nasaq/cm/files/aldlyl-alastrshady-ltrtyb-qwaaem-aljamaeat-hsb-almjalat-2027-2026.pdf';

    private const GUIDE_NOTES = 'الدليل الاسترشادي لترتيب قوائم الجامعات حسب المجالات 2026-2027 (مسارات الرواد والبحث والتطوير وإمداد)';

    private const PATH_PAGES = [
        'rowad' => 'https://sites.moe.gov.sa/scholarship-program/paths/path-arruaad/',
        'emdad' => 'https://sites.moe.gov.sa/scholarship-program/paths/path-emdad/',
        'research' => 'https://sites.moe.gov.sa/scholarship-program/paths/path-albahthwaltatwir/',
        'waed' => 'https://sites.moe.gov.sa/scholarship-program/paths/path-waaid/',
    ];

    public function up(): void
    {
        $guidePaths = DB::table('tracks')->whereIn('slug', ['rowad', 'emdad', 'research'])->pluck('id');

        DB::table('guides')->whereIn('path_id', $guidePaths)->where('url', self::OLD_GUIDE_URL)
            ->update(['url' => self::GUIDE_URL, 'updated_at' => now()]);

        DB::table('guides')->whereIn('path_id', $guidePaths)->where('url', self::GUIDE_URL)
            ->where(fn ($q) => $q->whereNull('notes')->orWhere('notes', ''))
            ->update(['notes' => self::GUIDE_NOTES]);

        foreach (self::PATH_PAGES as $slug => $url) {
            DB::table('tracks')->where('slug', $slug)->where('external_url', '')->update(['external_url' => $url]);
        }
    }

    public function down(): void
    {
        $guidePaths = DB::table('tracks')->whereIn('slug', ['rowad', 'emdad', 'research'])->pluck('id');

        DB::table('guides')->whereIn('path_id', $guidePaths)->where('url', self::GUIDE_URL)
            ->update(['url' => self::OLD_GUIDE_URL]);

        DB::table('guides')->whereIn('path_id', $guidePaths)->where('notes', self::GUIDE_NOTES)
            ->update(['notes' => '']);

        foreach (self::PATH_PAGES as $slug => $url) {
            DB::table('tracks')->where('slug', $slug)->where('external_url', $url)->update(['external_url' => '']);
        }
    }
};
