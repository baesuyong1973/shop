<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Unit;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Sales units (個, 箱, kg, ...) chosen on products.
 *
 * Super admins manage the shared units every shop can use; shop admins add
 * their own units, which only their shop's products can use.
 */
class UnitController extends Controller
{
    /** Field names for validation messages ("name" would otherwise read as 氏名). */
    private const ATTRIBUTES = ['name' => '単位名'];

    public function index(): Response
    {
        return Inertia::render('Admin/Units/Index', [
            'units' => Unit::whereNull('shop_id')->withCount('products')->orderBy('id')->get(),
            'status' => session('status'),
            'error' => session('error'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Units/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        // A shared unit shows up in every shop, so it must not clash with
        // any shop's own unit either.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('units', 'name')],
        ], attributes: self::ATTRIBUTES);

        $unit = Unit::create($data);

        return redirect()->route('admin.units.index')->with('status', "「{$unit->name}」を登録しました。");
    }

    public function edit(Unit $unit): Response
    {
        abort_unless($unit->shop_id === null, 404);

        return Inertia::render('Admin/Units/Edit', [
            'unit' => $unit->loadCount('products'),
        ]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        abort_unless($unit->shop_id === null, 404);

        $unit->update($request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('units', 'name')->ignore($unit)],
        ], attributes: self::ATTRIBUTES));

        return redirect()->route('admin.units.index')->with('status', "「{$unit->name}」を更新しました。");
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        abort_unless($unit->shop_id === null, 404);

        return $this->deleteUnlessUsed($unit, route('admin.units.index'));
    }

    /**
     * Shared units (read-only here) and this shop's own units.
     */
    public function shopIndex(Shop $shop): Response
    {
        return Inertia::render('Admin/Units/Index', [
            'shop' => $shop,
            'sharedUnits' => Unit::whereNull('shop_id')->orderBy('id')->get(['id', 'name']),
            'units' => $shop->units()->withCount('products')->orderBy('id')->get(),
            'status' => session('status'),
            'error' => session('error'),
        ]);
    }

    public function shopCreate(Shop $shop): Response
    {
        return Inertia::render('Admin/Units/Create', ['shop' => $shop]);
    }

    public function shopStore(Request $request, Shop $shop): RedirectResponse
    {
        $unit = $shop->units()->create($this->validateShopUnit($request, $shop));

        return redirect()->route('admin.shop.units.index', $shop)->with('status', "「{$unit->name}」を登録しました。");
    }

    public function shopEdit(Shop $shop, Unit $unit): Response
    {
        return Inertia::render('Admin/Units/Edit', [
            'shop' => $shop,
            'unit' => $unit->loadCount('products'),
        ]);
    }

    public function shopUpdate(Request $request, Shop $shop, Unit $unit): RedirectResponse
    {
        $unit->update($this->validateShopUnit($request, $shop, $unit));

        return redirect()->route('admin.shop.units.index', $shop)->with('status', "「{$unit->name}」を更新しました。");
    }

    public function shopDestroy(Shop $shop, Unit $unit): RedirectResponse
    {
        return $this->deleteUnlessUsed($unit, route('admin.shop.units.index', $shop));
    }

    /**
     * A shop's unit name must differ from the shared units and the shop's
     * other units, since both appear together in that shop's product form.
     */
    private function validateShopUnit(Request $request, Shop $shop, ?Unit $unit = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('units', 'name')
                    ->where(fn (Builder $q) => $q->whereNull('shop_id')->orWhere('shop_id', $shop->id))
                    ->ignore($unit),
            ],
        ], attributes: self::ATTRIBUTES);
    }

    private function deleteUnlessUsed(Unit $unit, string $redirectTo): RedirectResponse
    {
        if ($unit->products()->exists()) {
            return back()->with('error', "「{$unit->name}」は商品で使用中のため削除できません。");
        }

        $unit->delete();

        return redirect($redirectTo)->with('status', "「{$unit->name}」を削除しました。");
    }
}
