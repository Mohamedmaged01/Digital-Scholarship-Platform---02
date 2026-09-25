<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Support\Lists;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');

        return view('admin.news', [
            'items' => News::ordered()->get()->filter(fn (News $n) => Lists::matches($q, "{$n->title} {$n->excerpt} {$n->author}")),
            'editing' => $request->filled('edit') ? News::find($request->query('edit')) : null,
            'q' => $q,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        News::create($this->validated($request));

        return redirect()->route('admin.news.index')->with('status', 'تم نشر الخبر');
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $news->update($this->validated($request));

        return redirect()->route('admin.news.index')->with('status', 'تم حفظ التعديلات');
    }

    public function togglePin(News $news): RedirectResponse
    {
        $news->update(['pinned' => ! $news->pinned]);

        return back()->with('status', $news->pinned ? 'تم تثبيت الخبر' : 'تم إلغاء التثبيت');
    }

    public function destroy(News $news): RedirectResponse
    {
        $news->delete();

        return redirect()->route('admin.news.index')->with('status', 'تم حذف الخبر');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:6', 'max:255'],
            'excerpt' => ['required', 'string', 'min:6', 'max:1000'],
            'body' => ['required', 'string', 'min:10'],
            'category' => ['required', Rule::in(News::CATEGORIES)],
            'published_on' => ['nullable', 'date'],
            'author' => ['nullable', 'string', 'max:255'],
            'read_minutes' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        return [
            ...$data,
            'pinned' => $request->boolean('pinned'),
            'published_on' => $data['published_on'] ?? now()->toDateString(),
            'author' => ($data['author'] ?? '') ?: 'الإدارة العامة للابتعاث',
            'read_minutes' => $data['read_minutes'] ?? 3,
        ];
    }
}
