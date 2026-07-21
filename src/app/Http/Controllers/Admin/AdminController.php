<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Admins/Index', [
            'admins' => Admin::with('shop')->latest()->paginate(20)->withQueryString(),
            'status' => session('status'),
            'error' => session('error'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Admins/Create', [
            'shops' => Shop::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request, true);

        $admin = new Admin([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $admin->role = $data['role'];
        $admin->shop_id = $data['role'] === Admin::ROLE_SHOP_ADMIN ? $data['shop_id'] : null;
        $admin->save();

        return redirect()->route('admin.admins.index')->with('status', '管理者を登録しました。');
    }

    public function show(Admin $admin): Response
    {
        return Inertia::render('Admin/Admins/Show', [
            'admin' => $admin->load('shop'),
            'error' => session('error'),
        ]);
    }

    public function edit(Admin $admin): Response
    {
        return Inertia::render('Admin/Admins/Edit', [
            'admin' => $admin,
            'shops' => Shop::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Admin $admin): RedirectResponse
    {
        $data = $this->validateData($request, false, $admin);

        $admin->name = $data['name'];
        $admin->email = $data['email'];
        if (! empty($data['password'])) {
            $admin->password = $data['password'];
        }
        $admin->role = $data['role'];
        $admin->shop_id = $data['role'] === Admin::ROLE_SHOP_ADMIN ? $data['shop_id'] : null;
        $admin->save();

        return redirect()->route('admin.admins.index')->with('status', '管理者を更新しました。');
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->with('error', '自分自身のアカウントは削除できません。');
        }

        if (Admin::count() <= 1) {
            return back()->with('error', '最後の管理者アカウントは削除できません。');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')->with('status', '管理者を削除しました。');
    }

    /**
     * Log a super admin in as a shop admin, bypassing 2FA. Super admin only.
     */
    public function impersonate(Request $request, Admin $admin): RedirectResponse
    {
        abort_unless($admin->role === Admin::ROLE_SHOP_ADMIN, 403);

        $impersonatorId = Auth::guard('admin')->id();

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();
        $request->session()->put('impersonator_admin_id', $impersonatorId);

        return redirect()->route('admin.dashboard')->with('status', "「{$admin->name}」として店舗管理者ログインしました。");
    }

    /**
     * Return to the super admin account that started impersonation.
     */
    public function stopImpersonating(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->get('impersonator_admin_id');

        abort_unless($impersonatorId, 403);

        $original = Admin::findOrFail($impersonatorId);

        Auth::guard('admin')->login($original);
        $request->session()->regenerate();
        $request->session()->forget('impersonator_admin_id');

        return redirect()->route('admin.dashboard')->with('status', 'スーパー管理者に戻りました。');
    }

    private function validateData(Request $request, bool $passwordRequired, ?Admin $admin = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin)],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in([Admin::ROLE_SUPER_ADMIN, Admin::ROLE_SHOP_ADMIN])],
            'shop_id' => [Rule::requiredIf(fn () => $request->input('role') === Admin::ROLE_SHOP_ADMIN), 'nullable', 'integer', 'exists:shops,id'],
        ]);
    }
}
