<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class OrderController extends Controller
{
    public function show(Request $request, Shop $shop, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        return Inertia::render('Orders/Show', [
            'order' => $order->load('items.product', 'shop'),
        ]);
    }

    public function store(Request $request, Shop $shop): RedirectResponse
    {
        $cart = $request->session()->get("cart.{$shop->id}", []);

        if (empty($cart)) {
            return redirect()->route('cart.index', $shop)->with('error', 'カートに商品がありません。');
        }

        $products = $shop->products()->whereIn('id', array_keys($cart))->get()->keyBy('id');

        foreach ($cart as $productId => $quantity) {
            $product = $products->get($productId);

            if (! $product || ! $product->is_active || $quantity > $product->stock) {
                return redirect()->route('cart.index', $shop)->with('error', 'カート内の商品の在庫が不足しています。内容をご確認ください。');
            }
        }

        $initialStatus = OrderStatus::initialKey()
            ?? throw new RuntimeException('No initial order status is configured.');

        $order = DB::transaction(function () use ($request, $shop, $cart, $products, $initialStatus) {
            $order = Order::create([
                'shop_id' => $shop->id,
                'user_id' => $request->user()->id,
                'total_amount' => 0,
                'status' => $initialStatus,
            ]);

            $total = 0;

            foreach ($cart as $productId => $quantity) {
                $product = $products->get($productId);
                $subtotal = $product->price * $quantity;
                $total += $subtotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $quantity);
            }

            $order->update(['total_amount' => $total]);

            return $order;
        });

        $request->session()->forget("cart.{$shop->id}");

        return redirect()->route('shops.show', $shop)->with('status', "ご注文ありがとうございます。（注文番号：{$order->id}）");
    }
}
