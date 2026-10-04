<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMethod;
use App\Support\DefaultContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** وسائل الاتصال: تظهر في التذييل وفي ردّ المساعد عند غياب الإجابة. */
class ContactMethodController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.contact', [
            'items' => ContactMethod::orderBy('sort')->get(),
            'editing' => $request->filled('edit') ? ContactMethod::find($request->query('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ContactMethod::create($this->validated($request));

        return redirect()->route('admin.contact.index')->with('status', 'تمت إضافة وسيلة الاتصال');
    }

    public function update(Request $request, ContactMethod $contact): RedirectResponse
    {
        $contact->update($this->validated($request));

        return redirect()->route('admin.contact.index')->with('status', 'تم حفظ التعديلات');
    }

    public function destroy(ContactMethod $contact): RedirectResponse
    {
        $contact->delete();

        return back()->with('status', 'تم حذف وسيلة الاتصال');
    }

    public function reset(): RedirectResponse
    {
        DefaultContent::resetContactMethods();

        return redirect()->route('admin.contact.index')->with('status', 'تمت استعادة وسائل الاتصال الافتراضية');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'min:2', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
            // روابط آمنة فقط: هاتف أو بريد أو https
            'href' => ['nullable', 'string', 'max:500', 'regex:/^(tel:|mailto:|https?:\/\/)/i'],
            'icon' => ['required', Rule::in(ContactMethod::ICONS)],
            'sort' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], ['href.regex' => 'يجب أن يبدأ الرابط بـ tel: أو mailto: أو https://']);

        return [...$data, 'href' => $data['href'] ?? '', 'sort' => $data['sort'] ?? 0];
    }
}
