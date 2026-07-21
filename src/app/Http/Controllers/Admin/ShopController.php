<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;

class ShopController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Shops/Index', [
            'shops' => Shop::orderBy('name')->paginate(20)->withQueryString(),
            'status' => session('status'),
            'error' => session('error'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Shops/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request, null);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $this->processAndStoreImage($request->file('logo'));
        }
        unset($data['logo']);

        Shop::create($data);

        return redirect()->route('admin.shops.index')->with('status', '店舗を登録しました。');
    }

    public function edit(Shop $shop): Response
    {
        return Inertia::render('Admin/Shops/Edit', [
            'shop' => $shop,
        ]);
    }

    public function update(Request $request, Shop $shop): RedirectResponse
    {
        $data = $this->validateData($request, $shop);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $this->processAndStoreImage($request->file('logo'));
            if ($shop->logo_path) {
                Storage::disk('public')->delete($shop->logo_path);
            }
        }
        unset($data['logo']);

        $shop->update($data);

        return redirect()->route('admin.shops.index')->with('status', '店舗を更新しました。');
    }

    public function destroy(Shop $shop): RedirectResponse
    {
        if ($shop->products()->exists() || $shop->orders()->exists() || $shop->admins()->exists()) {
            return back()->with('error', '商品・注文・管理者が紐付いている店舗は削除できません。');
        }

        if ($shop->logo_path) {
            Storage::disk('public')->delete($shop->logo_path);
        }

        $shop->delete();

        return redirect()->route('admin.shops.index')->with('status', '店舗を削除しました。');
    }

    private function processAndStoreImage(UploadedFile $file): string
    {
        $manager = new ImageManager(Driver::class);

        $encoded = $manager->decodePath($file->getRealPath())
            ->scaleDown(width: 600, height: 600)
            ->encode(new PngEncoder);

        $filename = 'shops/'.Str::random(40).'.png';

        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }

    private function validateData(Request $request, ?Shop $shop): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('shops', 'slug')->ignore($shop),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'max:5120'],
            'is_active' => ['boolean'],
        ]);
    }
}
