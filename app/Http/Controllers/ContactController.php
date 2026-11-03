<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\ContactMethod;
use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** صفحة «اتصل بنا»: معلومات التواصل ونموذج تُحفظ رسائله في لوحة التحكم. */
class ContactController extends Controller
{
    public function show(Request $request): View
    {
        return view('contact', [
            'methods' => ContactMethod::orderBy('sort')->get(),
            'socials' => SocialLink::orderBy('sort')->get(),
            // القادم من زر «تواصل مع الموظف المسؤول» في المساعد الذكي
            'fromAssistant' => $request->query('from') === 'assistant',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // حقل مخفي لا يملؤه إلا البرامج الآلية
        if (filled($request->input('website'))) {
            return redirect()->route('contact.show')->with('sent', true);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:30', 'regex:/^\+?[\d\s()-]{7,20}$/'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:160'],
            'subject' => ['required', 'string', 'min:3', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'source' => ['nullable', Rule::in(['page', 'assistant'])],
        ]);

        ContactMessage::create([
            ...$data,
            'phone' => $data['phone'] ?? '',
            'email' => $data['email'] ?? '',
            'source' => $data['source'] ?? 'page',
        ]);

        return redirect()->route('contact.show')->with('sent', true);
    }
}
