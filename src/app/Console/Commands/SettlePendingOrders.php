<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Payments\DepositPayments;
use App\Payments\PaymentException;
use Illuminate\Console\Command;

/**
 * Resolves orders whose deposit is still pending after the payment window,
 * e.g. when the customer closed the browser on the payment screen: confirms
 * them if the payment went through after all, otherwise cancels them and
 * puts their stock back.
 */
class SettlePendingOrders extends Command
{
    protected $signature = 'orders:settle-pending';

    protected $description = '支払い待ちのまま時間が過ぎた注文を確定または取り消す';

    public function handle(DepositPayments $payments): int
    {
        if (! $payments->enabled()) {
            return self::SUCCESS;
        }

        $orders = Order::where('payment_status', Order::PAYMENT_PENDING)
            ->where('created_at', '<', now()->subMinutes(config('payment.pending_minutes')))
            ->get();

        foreach ($orders as $order) {
            try {
                $paid = $payments->settle($order);
                $this->line("注文 {$order->id}: ".($paid ? '支払い済みのため確定' : '未払いのため取り消し'));
            } catch (PaymentException $e) {
                report($e);
                $this->warn("注文 {$order->id}: 支払い状況を確認できませんでした（次回再試行）");
            }
        }

        return self::SUCCESS;
    }
}
