<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** حسابات التواصل الاجتماعي في التذييل — تُدار من صفحة «وسائل الاتصال». */
class SocialLinkController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        SocialLink::create($this->validated($request));

        return redirect()->route('admin.contact.index')->with('status', 'تمت إضافة حساب التواصل');
    }

    public function update(Request $request, SocialLink $social): RedirectResponse
    {
        $social->update($this->validated($request));

        return redirect()->route('admin.contact.index')->with('status', 'تم حفظ التعديلات');
    }

    public function destroy(SocialLink $social): RedirectResponse
    {
        $social->delete();

        return back()->with('status', 'تم حذف حساب التواصل');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'platform' => ['required', Rule::in(array_keys(config('kasp.social_platforms')))],
            'url' => ['required', 'url:https', 'max:500'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], ['url.url' => 'يجب أن يبدأ رابط الحساب بـ https://']);

        return [...$data, 'sort' => $data['sort'] ?? 0];
    }
}
