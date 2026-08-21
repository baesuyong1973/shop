<?php

namespace App\Http\Controllers;

use App\Mail\ContactInquiry;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Contact/Create', [
            'completed' => (bool) session('completed'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'type' => ['required', 'in:bug,shop_registration,other'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $adminEmails = Admin::where('role', Admin::ROLE_SUPER_ADMIN)->pluck('email');

        if ($adminEmails->isNotEmpty()) {
            Mail::to($adminEmails)->send(new ContactInquiry(...$validated));
        }

        return redirect()->route('contact.create')->with('completed', true);
    }
}
