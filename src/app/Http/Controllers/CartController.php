<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Payments\DepositPayments;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(Request $request, Shop $shop, DepositPayments $payments): Response
    {
        $cart = $request->session()->get("cart.{$shop->id}", []);

        $products = Product::with('country', 'prefecture', 'unit')->whereIn('id', array_keys($cart))->get();

        $items = $products->map(fn (Product $product) => [
            'product' => $product,
            'quantity' => $cart[$product->id],
            'subtotal' => $product->price * $cart[$product->id],
        ])->values();

        $total = $items->sum('subtotal');

        return Inertia::render('Cart/Index', [
            'shop' => $shop,
            'items' => $items,
            'total' => $total,
            // Paid up front via PayPay at checkout; null when deposits are off.
            'deposit' => $payments->enabled() ? [
                'rate' => config('payment.deposit_rate'),
                'amount' => Order::depositFor($total),
                'remaining' => $total - Order::depositFor($total),
            ] : null,
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

        return redirect()->route('cart.index', $shop)->with('status', __('messages.cart.added'));
    }

    public function destroy(Request $request, Shop $shop, Product $product): RedirectResponse
    {
        $cart = $request->session()->get("cart.{$shop->id}", []);
        unset($cart[$product->id]);
        $request->session()->put("cart.{$shop->id}", $cart);

        return redirect()->route('cart.index', $shop)->with('status', __('messages.cart.removed'));
    }
}
