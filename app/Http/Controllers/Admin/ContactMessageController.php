<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Support\Lists;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** صندوق رسائل «اتصل بنا»: الجديد منها منذ آخر اطلاع لكل مستخدم يظهر تنبيهًا في اللوحة. */
class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $q = (string) $request->query('q', '');
        $since = $user->messages_seen_at;

        $open = $request->filled('open') ? ContactMessage::find($request->query('open')) : null;
        if ($open && ! $open->read_at) {
            $open->forceFill(['read_at' => now()])->save();
        }

        $view = view('admin.messages', [
            'items' => ContactMessage::latest()->get()->filter(fn (ContactMessage $m) => Lists::matches($q, "{$m->name} {$m->email} {$m->phone} {$m->subject} {$m->message}")),
            'open' => $open,
            'since' => $since,
            'q' => $q,
        ]);

        // فتح الصندوق يعني الاطلاع على الجديد — يختفي التنبيه لهذا المستخدم
        $user->forceFill(['messages_seen_at' => now()])->save();

        return $view;
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('status', 'تم حذف الرسالة');
    }
}
