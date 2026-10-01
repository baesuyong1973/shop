<?php

namespace App\Payments;

final readonly class PaymentResult
{
    public function __construct(
        public bool $completed,
        /** Provider's id for the completed payment (needed for refunds). */
        public ?string $paymentId = null,
    ) {}
}
