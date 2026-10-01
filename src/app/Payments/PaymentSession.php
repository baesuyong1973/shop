<?php

namespace App\Payments;

final readonly class PaymentSession
{
    public function __construct(
        /** Where the customer completes the payment. */
        public string $paymentUrl,
        /** Provider handle needed to cancel the unpaid payment later. */
        public ?string $codeId = null,
    ) {}
}
