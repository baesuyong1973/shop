<?php

namespace App\Payments;

use App\Mail\AdminOrderNotification;
use App\Mail\OrderConfirmed;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Order deposits: part of the total is paid up front (via the configured
 * gateway) before the order is confirmed; the rest is paid at pickup.
 *
 * An order with a deposit stays "pending" until settle() sees the payment
 * completed. Unpaid orders are expired: cancelled with their stock restored.
 */
class DepositPayments
{
    public function __construct(private readonly ?DepositGateway $gateway) {}

    /**
     * Whether new orders require a deposit (a payment driver is configured).
     */
    public function enabled(): bool
    {
        return $this->gateway !== null;
    }

    /**
     * Put a freshly created order into "pending" and start its deposit
     * payment. Returns the URL where the customer pays.
     *
     * @throws PaymentException
     */
    public function start(Order $order, string $returnUrl): string
    {
        $order->forceFill([
            'deposit_amount' => Order::depositFor($order->total_amount),
            'payment_status' => Order::PAYMENT_PENDING,
            'payment_reference' => "order-{$order->id}-".Str::lower(Str::random(12)),
        ])->save();

        try {
            $session = $this->gateway->createPayment($order, $returnUrl);
        } catch (PaymentException $e) {
            // Nothing was created on the provider's side, so just undo the order.
            $this->voidOrder($order);
            $order->forceFill(['payment_status' => Order::PAYMENT_EXPIRED])->save();

            throw $e;
        }

        $order->forceFill(['payment_code_id' => $session->codeId])->save();

        return $session->paymentUrl;
    }

    /**
     * Resolve a pending order: confirm it if the deposit was paid, otherwise
     * stop the payment and expire the order. Safe to call repeatedly and
     * concurrently. Returns whether the order ended up paid.
     *
     * @throws PaymentException when the provider can't be reached; the order
     *                          stays pending so a later call can retry
     */
    public function settle(Order $order): bool
    {
        $confirmed = DB::transaction(function () use ($order) {
            $order = Order::lockForUpdate()->findOrFail($order->id);

            if ($order->payment_status !== Order::PAYMENT_PENDING) {
                return null;
            }

            $result = $this->gateway->fetchPayment($order);

            if ($result->completed) {
                $order->forceFill([
                    'payment_status' => Order::PAYMENT_PAID,
                    'payment_id' => $result->paymentId,
                    'paid_at' => now(),
                ])->save();

                return $order;
            }

            $this->gateway->cancelPayment($order);
            $this->voidOrder($order);
            $order->forceFill(['payment_status' => Order::PAYMENT_EXPIRED])->save();

            return null;
        });

        if ($confirmed) {
            $this->sendConfirmation($confirmed);
        }

        return $order->fresh()->payment_status === Order::PAYMENT_PAID;
    }

    /**
     * Refund a paid deposit (the order is being cancelled).
     *
     * @throws PaymentException
     */
    public function refund(Order $order): void
    {
        if ($order->payment_status !== Order::PAYMENT_PAID) {
            return;
        }

        $this->gateway->refund($order);

        $order->forceFill([
            'payment_status' => Order::PAYMENT_REFUNDED,
            'refunded_at' => now(),
        ])->save();
    }

    /**
     * Tell the customer and the shop's admins about a confirmed order.
     */
    public function sendConfirmation(Order $order): void
    {
        $order->loadMissing('items', 'shop', 'user');

        Mail::to($order->user->email)->send(new OrderConfirmed($order));

        $adminEmails = $order->shop->admins()->pluck('email');

        if ($adminEmails->isNotEmpty()) {
            Mail::to($adminEmails)->send(new AdminOrderNotification($order));
        }
    }

    /**
     * Move the order to a void status and put its items back in stock.
     */
    private function voidOrder(Order $order): void
    {
        $voidKey = OrderStatus::where('is_void', true)->orderBy('sort_order')->value('key')
            ?? throw new RuntimeException('No void order status is configured.');

        foreach ($order->items()->with('product')->get() as $item) {
            $item->product?->increment('stock', $item->quantity);
        }

        $order->forceFill(['status' => $voidKey])->save();
    }
}
