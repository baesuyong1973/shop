<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(Request $request, Shop $shop): Response
    {
        $cart = $request->session()->get("cart.{$shop->id}", []);

        $products = Product::with('prefecture', 'unit')->whereIn('id', array_keys($cart))->get();

        $items = $products->map(fn (Product $product) => [
            'product' => $product,
            'quantity' => $cart[$product->id],
            'subtotal' => $product->price * $cart[$product->id],
        ])->values();

        return Inertia::render('Cart/Index', [
            'shop' => $shop,
            'items' => $items,
            'total' => $items->sum('subtotal'),
            'status' => session('status'),
            'error' => session('error'),
        ]);
    }

    public function store(Request $request, Shop $shop, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$product->stock],
        ]);

        $cart = $request->session()->get("cart.{$shop->id}", []);
        $currentQuantity = $cart[$product->id] ?? 0;
        $cart[$product->id] = min($currentQuantity + $data['quantity'], $product->stock);

        $request->session()->put("cart.{$shop->id}", $cart);

        return redirect()->route('cart.index', $shop)->with('status', 'カートに追加しました。');
    }

    public function destroy(Request $request, Shop $shop, Product $product): RedirectResponse
    {
        $cart = $request->session()->get("cart.{$shop->id}", []);
        unset($cart[$product->id]);
        $request->session()->put("cart.{$shop->id}", $cart);

        return redirect()->route('cart.index', $shop)->with('status', 'カートから削除しました。');
    }
}
