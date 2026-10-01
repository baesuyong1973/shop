<?php

namespace App\Payments;

use App\Models\Order;

/**
 * Collects an order's deposit through an external payment service.
 *
 * Implementations identify the payment by $order->payment_reference, which
 * the caller sets before createPayment().
 */
interface DepositGateway
{
    /**
     * Start a payment for the order's deposit and return the URL the
     * customer pays at. The customer is sent back to $returnUrl afterwards.
     *
     * @throws PaymentException
     */
    public function createPayment(Order $order, string $returnUrl): PaymentSession;

    /**
     * Current state of the order's payment.
     *
     * @throws PaymentException
     */
    public function fetchPayment(Order $order): PaymentResult;

    /**
     * Stop an unpaid payment so it can no longer be completed.
     *
     * @throws PaymentException
     */
    public function cancelPayment(Order $order): void;

    /**
     * Refund the order's paid deposit in full.
     *
     * @throws PaymentException
     */
    public function refund(Order $order): void;
}
