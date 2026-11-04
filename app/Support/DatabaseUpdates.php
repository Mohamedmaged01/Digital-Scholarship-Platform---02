<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

/**
 * تحديثات قاعدة البيانات (migrations) التي وصلت مع الكود ولم تُطبَّق بعد.
 * الاستضافة قد لا تتيح تشغيل الأوامر، فيطبّقها مدير النظام من لوحة التحكم بزر واحد.
 */
class DatabaseUpdates
{
    /** @return list<string> أسماء ملفات التحديث المعلّقة بالترتيب */
    public static function pending(): array
    {
        return rescue(function () {
            $migrator = app('migrator');
            if (! $migrator->repositoryExists()) {
                return ['migrations'];
            }
            $files = $migrator->getMigrationFiles([database_path('migrations')]);

            return array_values(array_diff(array_keys($files), $migrator->getRepository()->getRan()));
        }, [], false);
    }

    /** يطبّق التحديثات المعلّقة ويعيد ناتج الأمر (قفل يمنع التشغيل المتزامن). */
    public static function apply(): string
    {
        return Cache::lock('database-updates', 300)->get(function () {
            Artisan::call('migrate', ['--force' => true]);

            return trim(Artisan::output());
        }) ?? __('يجري تطبيق التحديثات حاليًا من جلسة أخرى، أعد المحاولة بعد قليل.');
    }
}
