<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminTwoFactorCode;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Admin/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::where('email', $credentials['email'])->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'email' => 'メールアドレスまたはパスワードが正しくありません。',
            ]);
        }

        $this->issueTwoFactorCode($admin);

        $request->session()->put('admin_2fa_id', $admin->id);

        return redirect()->route('admin.verify.show');
    }

    public function showVerify(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has('admin_2fa_id')) {
            return redirect()->route('admin.login');
        }

        return Inertia::render('Admin/Verify', [
            'status' => session('status'),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $adminId = $request->session()->get('admin_2fa_id');

        if (! $adminId) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find($adminId);

        if (! $admin
            || ! $admin->two_factor_code
            || ! $admin->two_factor_expires_at
            || $admin->two_factor_expires_at->isPast()
            || ! Hash::check($request->string('code'), $admin->two_factor_code)
        ) {
            throw ValidationException::withMessages([
                'code' => '確認コードが正しくないか、有効期限が切れています。',
            ]);
        }

        $admin->forceFill([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ])->save();

        $request->session()->forget('admin_2fa_id');
        $request->session()->regenerate();

        Auth::guard('admin')->login($admin);

        return redirect()->route('admin.dashboard');
    }

    public function resend(Request $request): RedirectResponse
    {
        $adminId = $request->session()->get('admin_2fa_id');

        if (! $adminId) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find($adminId);

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        $this->issueTwoFactorCode($admin);

        return back()->with('status', '確認コードを再送しました。');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function issueTwoFactorCode(Admin $admin): void
    {
        $code = (string) random_int(100000, 999999);

        $admin->forceFill([
            'two_factor_code' => Hash::make($code),
            'two_factor_expires_at' => now()->addMinutes(10),
        ])->save();

        Mail::to($admin->email)->send(new AdminTwoFactorCode($code));
    }
}
