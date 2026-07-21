<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prefecture;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class ProductController extends Controller
{
    /**
     * Cross-shop product listing for super admins.
     */
    public function globalIndex(): Response
    {
        return Inertia::render('Admin/Products/Index', [
            'products' => Product::with(['shop', 'prefecture', 'unit'])->latest()->paginate(20)->withQueryString(),
            'shops' => Shop::orderBy('name')->get(['id', 'name']),
            'status' => session('status'),
        ]);
    }

    /**
     * Duplicate a product into another (or the same) shop. Always created
     * unpublished so it can be reviewed before going live at the target shop.
     */
    public function copy(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'shop_id' => ['required', 'integer', 'exists:shops,id'],
        ]);

        $copy = $product->replicate();
        $copy->shop_id = $data['shop_id'];
        $copy->is_active = false;
        $copy->arrival_date = now()->toDateString();
        $copy->save();

        return back()->with('status', '商品をコピーしました（非公開で登録されました）。');
    }

    public function index(Shop $shop): Response
    {
        return Inertia::render('Admin/Products/Index', [
            'shop' => $shop,
            'products' => $shop->products()->with(['prefecture', 'unit'])->latest()->paginate(20)->withQueryString(),
            'status' => session('status'),
        ]);
    }

    public function create(Shop $shop): Response
    {
        return Inertia::render('Admin/Products/Create', [
            'shop' => $shop,
            'prefectures' => Prefecture::orderBy('id')->get(['id', 'name']),
            'units' => Unit::orderBy('id')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, Shop $shop): RedirectResponse
    {
        $data = $this->validateData($request, true);

        $data['image_path'] = $this->processAndStoreImage($request->file('image'));
        unset($data['image']);

        $shop->products()->create($data);

        return redirect()->route('admin.shop.products.index', $shop)->with('status', '商品を登録しました。');
    }

    public function edit(Shop $shop, Product $product): Response
    {
        return Inertia::render('Admin/Products/Edit', [
            'shop' => $shop,
            'product' => $product,
            'prefectures' => Prefecture::orderBy('id')->get(['id', 'name']),
            'units' => Unit::orderBy('id')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Shop $shop, Product $product): RedirectResponse
    {
        $data = $this->validateData($request, false);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->processAndStoreImage($request->file('image'));
            Storage::disk('public')->delete($product->image_path);
        }
        unset($data['image']);

        $product->update($data);

        return redirect()->route('admin.shop.products.index', $shop)->with('status', '商品を更新しました。');
    }

    public function destroy(Shop $shop, Product $product): RedirectResponse
    {
        Storage::disk('public')->delete($product->image_path);
        $product->delete();

        return redirect()->route('admin.shop.products.index', $shop)->with('status', '商品を削除しました。');
    }

    private function processAndStoreImage(UploadedFile $file): string
    {
        $manager = new ImageManager(Driver::class);

        $encoded = $manager->decodePath($file->getRealPath())
            ->scaleDown(width: 1200, height: 1200)
            ->encode(new JpegEncoder(quality: 75));

        $filename = 'products/'.Str::random(40).'.jpg';

        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }

    private function validateData(Request $request, bool $imageRequired): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'max:5120'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'prefecture_id' => ['nullable', 'integer', 'exists:prefectures,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'unit_quantity' => ['nullable', 'integer', 'min:1'],
            'arrival_date' => ['nullable', 'date'],
        ]);
    }
}
