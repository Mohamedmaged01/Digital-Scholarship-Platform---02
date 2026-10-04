<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use App\Support\DefaultContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** السياسات والشروط: صفحات نصية تظهر روابطها في تذييل المنصة. */
class LegalPageController extends Controller
{
    public function index(Request $request): View
    {
        $pages = LegalPage::orderBy('sort')->get();
        $editing = $request->query('edit') === 'new' ? null
            : ($pages->firstWhere('id', (int) $request->query('edit')) ?? $pages->first());

        return view('admin.pages', [
            'pages' => $pages,
            'editing' => $editing,
            'creating' => $request->query('edit') === 'new' || $pages->isEmpty(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $page = LegalPage::create($this->validated($request) + ['sort' => (int) LegalPage::max('sort') + 1]);

        return redirect()->route('admin.pages.index', ['edit' => $page->id])->with('status', 'تمت إضافة الصفحة');
    }

    public function update(Request $request, LegalPage $page): RedirectResponse
    {
        $page->update($this->validated($request, $page));

        return redirect()->route('admin.pages.index', ['edit' => $page->id])->with('status', 'تم حفظ الصفحة');
    }

    public function destroy(LegalPage $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('status', 'تم حذف الصفحة');
    }

    public function reset(): RedirectResponse
    {
        DefaultContent::resetPages();

        return redirect()->route('admin.pages.index')->with('status', 'تمت استعادة النصوص الافتراضية');
    }

    private function validated(Request $request, ?LegalPage $page = null): array
    {
        return $request->validate([
            'title_ar' => ['required', 'string', 'min:3', 'max:255'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:60', Rule::unique('legal_pages', 'slug')->ignore($page)],
            'content' => ['required', 'string', 'min:20'],
        ]);
    }
}
