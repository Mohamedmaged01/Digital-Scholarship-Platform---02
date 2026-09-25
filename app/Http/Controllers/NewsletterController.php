<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('newsletter', [
            'email' => ['required', 'email', 'max:190'],
        ], [], ['email' => 'البريد الإلكتروني']);

        NewsletterSubscriber::firstOrCreate(['email' => mb_strtolower($data['email'])]);

        return redirect()->to(url()->previous().'#newsletter')->with('subscribed', true);
    }
}
