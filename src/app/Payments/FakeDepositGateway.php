<?php

namespace App\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;

/**
 * Stand-in for PayPay in development and tests. The "payment page" is a
 * local route (FakePayPayController) where the payer chooses to pay or not.
 * Never used in production.
 */
class FakeDepositGateway implements DepositGateway
{
    public const STATUS_CREATED = 'CREATED';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_CANCELED = 'CANCELED';

    public const STATUS_REFUNDED = 'REFUNDED';

    public function createPayment(Order $order, string $returnUrl): PaymentSession
    {
        self::put($order->payment_reference, [
            'status' => self::STATUS_CREATED,
            'amount' => $order->deposit_amount,
            'order_id' => $order->id,
            'return_url' => $returnUrl,
        ]);

        return new PaymentSession(route('fake-paypay.show', $order->payment_reference), "code-{$order->payment_reference}");
    }

    public function fetchPayment(Order $order): PaymentResult
    {
        $payment = self::get($order->payment_reference) ?? throw new PaymentException('Unknown fake payment.');

        $completed = in_array($payment['status'], [self::STATUS_COMPLETED, self::STATUS_REFUNDED], true);

        return new PaymentResult($completed, $completed ? "fake-{$order->payment_reference}" : null);
    }

    public function cancelPayment(Order $order): void
    {
        $payment = self::get($order->payment_reference);

        if ($payment && $payment['status'] === self::STATUS_CREATED) {
            self::put($order->payment_reference, ['status' => self::STATUS_CANCELED] + $payment);
        }
    }

    public function refund(Order $order): void
    {
        $payment = self::get($order->payment_reference);

        if (! $payment || $payment['status'] !== self::STATUS_COMPLETED) {
            throw new PaymentException('Only a completed fake payment can be refunded.');
        }

        self::put($order->payment_reference, ['status' => self::STATUS_REFUNDED] + $payment);
    }

    /**
     * @return array{status: string, amount: int, order_id: int, return_url: string}|null
     */
    public static function get(string $reference): ?array
    {
        return Cache::get("fake-paypay:{$reference}");
    }

    public static function put(string $reference, array $payment): void
    {
        Cache::put("fake-paypay:{$reference}", $payment, now()->addDay());
    }
}
