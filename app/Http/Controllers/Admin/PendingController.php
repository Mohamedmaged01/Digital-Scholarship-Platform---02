<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnansweredQuestion;
use App\Support\Lists;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendingController extends Controller
{
    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');

        return view('admin.pending', [
            'items' => UnansweredQuestion::orderByDesc('asked_count')->orderByDesc('last_asked_at')->get()
                ->filter(fn ($u) => Lists::matches($q, $u->question)),
            'q' => $q,
        ]);
    }

    /** نقل السؤال إلى نموذج قاعدة المعرفة لكتابة إجابته */
    public function answer(UnansweredQuestion $question): RedirectResponse
    {
        $text = $question->question;
        $question->delete();

        return redirect()->route('admin.kb.index', ['prefill' => $text]);
    }

    public function destroy(UnansweredQuestion $question): RedirectResponse
    {
        $question->delete();

        return back()->with('status', 'تم تجاهل السؤال');
    }
}
