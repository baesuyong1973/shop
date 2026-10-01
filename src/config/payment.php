<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Deposit Payment Driver
    |--------------------------------------------------------------------------
    |
    | How the order deposit is collected:
    |   null     - no deposit; orders are confirmed immediately (as before)
    |   "fake"   - a local stand-in for PayPay, for development and tests
    |   "paypay" - PayPay Open Payment API (sandbox unless "production")
    |
    */

    'driver' => env('PAYMENT_DRIVER'),

    // Percentage of the order total paid up front, rounded up to the yen.
    'deposit_rate' => (int) env('DEPOSIT_RATE', 1),

    // Unpaid orders older than this are cancelled and their stock restored.
    'pending_minutes' => (int) env('PAYMENT_PENDING_MINUTES', 30),

    'paypay' => [
        'api_key' => env('PAYPAY_API_KEY'),
        'api_secret' => env('PAYPAY_API_SECRET'),
        'merchant_id' => env('PAYPAY_MERCHANT_ID'),
        'production' => (bool) env('PAYPAY_PRODUCTION', false),
    ],

];
