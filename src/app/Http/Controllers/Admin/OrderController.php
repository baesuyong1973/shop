<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Cross-shop order listing for super admins.
     */
    public function globalIndex(): Response
    {
        return Inertia::render('Admin/Orders/Index', [
            'orders' => Order::with(['user', 'shop'])->latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function globalShow(Order $order): Response
    {
        return Inertia::render('Admin/Orders/Show', [
            'order' => $order->load('user', 'shop', 'items.product'),
            'status' => session('status'),
        ]);
    }

    public function globalUpdateStatus(Request $request, Order $order): RedirectResponse
    {
        $this->applyStatusChange($request, $order);

        return back()->with('status', '注文のステータスを更新しました。');
    }

    public function index(Shop $shop): Response
    {
        return Inertia::render('Admin/Orders/Index', [
            'shop' => $shop,
            'orders' => $shop->orders()
                ->whereNotIn('status', OrderStatus::voidKeys())
                ->with(['user', 'items.product.unit'])
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function show(Shop $shop, Order $order): Response
    {
        return Inertia::render('Admin/Orders/Show', [
            'shop' => $shop,
            'order' => $order->load('user', 'items.product'),
            'status' => session('status'),
        ]);
    }

    public function updateStatus(Request $request, Shop $shop, Order $order): RedirectResponse
    {
        $this->applyStatusChange($request, $order);

        return back()->with('status', '注文のステータスを更新しました。');
    }

    /**
     * Product-wise totals across this shop's orders, optionally filtered to
     * a date range. Excludes cancelled orders since there's nothing to
     * prepare for those.
     */
    public function summary(Request $request, Shop $shop): Response
    {
        $data = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $summary = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->where('orders.shop_id', $shop->id)
            ->whereNotIn('orders.status', OrderStatus::voidKeys())
            ->when(
                ! empty($data['date_from']),
                fn ($query) => $query->whereDate('orders.created_at', '>=', $data['date_from']),
            )
            ->when(
                ! empty($data['date_to']),
                fn ($query) => $query->whereDate('orders.created_at', '<=', $data['date_to']),
            )
            ->selectRaw('order_items.product_name, units.name as unit_name, SUM(order_items.quantity) as total_quantity')
            ->groupBy('order_items.product_name', 'units.name')
            ->orderByDesc('total_quantity')
            ->get();

        return Inertia::render('Admin/Orders/Summary', [
            'shop' => $shop,
            'summary' => $summary,
            'dateFrom' => $data['date_from'] ?? null,
            'dateTo' => $data['date_to'] ?? null,
        ]);
    }

    /**
     * Move an order to a status reachable from its current one, per the
     * admin-configured order status transitions. Moving into a "void"
     * status (e.g. cancellation) restores stock for each item.
     */
    private function applyStatusChange(Request $request, Order $order): void
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'exists:order_statuses,key'],
        ]);

        $current = OrderStatus::where('key', $order->status)->first();
        $target = OrderStatus::where('key', $data['status'])->first();

        abort_unless(
            $current && $target && $current->nextStatuses()->whereKey($target->id)->exists(),
            422,
            'このステータスの注文は変更できません。'
        );

        DB::transaction(function () use ($order, $target) {
            if ($target->is_void) {
                $order->loadMissing('items.product');

                foreach ($order->items as $item) {
                    $item->product?->increment('stock', $item->quantity);
                }
            }

            $order->update(['status' => $target->key]);
        });
    }
}
