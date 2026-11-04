<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\DatabaseUpdates;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

/** تطبيق تحديثات قاعدة البيانات من اللوحة (بديل `php artisan migrate --force` على الخادم). */
class SystemController extends Controller
{
    public function migrate(Request $request): RedirectResponse
    {
        $pending = DatabaseUpdates::pending();
        if ($pending === []) {
            return back()->with('status', 'قاعدة البيانات محدّثة — لا توجد تحديثات معلّقة.');
        }

        try {
            $output = DatabaseUpdates::apply();
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['migrate' => 'تعذّر تطبيق تحديثات قاعدة البيانات: '.$e->getMessage()]);
        }

        $left = DatabaseUpdates::pending();
        rescue(fn () => AuditLog::create([
            'admin_id' => $request->user()->id, 'admin_email' => $request->user()->email,
            'action' => 'migrate', 'entity_type' => 'database', 'new_value' => ['applied' => array_values(array_diff($pending, $left))],
            'ip' => $request->ip(), 'notes' => mb_substr($output, 0, 2000),
        ]), report: false);

        return $left === []
            ? back()->with('status', 'تم تطبيق '.count($pending).' من تحديثات قاعدة البيانات بنجاح.')
            : back()->withErrors(['migrate' => 'بقيت تحديثات لم تُطبَّق: '.implode('، ', $left)]);
    }
}
