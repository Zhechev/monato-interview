<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Marketplace Settings
    |--------------------------------------------------------------------------
    |
    | These settings control various aspects of the marketplace functionality.
    |
    */

    // Default commission rate (used when no active commission rule is found)
    'commission_rate' => env('MARKETPLACE_COMMISSION_RATE', 0.10),

    // Product status options
    'product_status' => [
        'active' => 'active',
        'inactive' => 'inactive',
    ],

    // Transaction types
    'transaction_types' => [
        'purchase' => 'purchase',
        'sale' => 'sale',
        'commission' => 'commission',
        'top_up' => 'top_up',
        'withdrawal' => 'withdrawal',
    ],
];
