<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KbEntry;
use App\Support\Lists;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KbController extends Controller
{
    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');
        $all = KbEntry::orderByDesc('id')->get()
            ->filter(fn (KbEntry $e) => Lists::matches($q, "{$e->question} {$e->answer} ".implode(' ', $e->keywords)));

        return view('admin.kb', [
            'customs' => $all->where('is_custom', true),
            'core' => $all->where('is_custom', false)->sortBy('id'),
            'editing' => $request->filled('edit') ? KbEntry::where('is_custom', true)->find($request->query('edit')) : null,
            'prefill' => (string) $request->query('prefill', ''),
            'q' => $q,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        KbEntry::create($this->validated($request) + ['is_custom' => true]);

        return redirect()->route('admin.kb.index')->with('status', 'تمت إضافة الإجابة إلى القاعدة');
    }

    public function update(Request $request, KbEntry $entry): RedirectResponse
    {
        abort_unless($entry->is_custom, 403, 'الإجابات الأساسية للنظام للقراءة فقط');
        $entry->update($this->validated($request));

        return redirect()->route('admin.kb.index')->with('status', 'تم حفظ التعديلات');
    }

    public function destroy(KbEntry $entry): RedirectResponse
    {
        abort_unless($entry->is_custom, 403, 'الإجابات الأساسية للنظام للقراءة فقط');
        $entry->delete();

        return redirect()->route('admin.kb.index')->with('status', 'تم حذف الإجابة');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'min:4', 'max:500'],
            'answer' => ['required', 'string', 'min:4'],
            'keywords' => ['nullable', 'string', 'max:1000'],
        ]);

        return [...$data, 'keywords' => Lists::split($data['keywords'] ?? '')];
    }
}
