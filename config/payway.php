<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayWay Merchant Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for ABA PayWay payment gateway integration
    |
    */

    'merchant_id' => env('PAYWAY_MERCHANT_ID', ''),
    
    'api_key' => env('PAYWAY_API_KEY', ''),
    
    'base_url' => env('PAYWAY_BASE_URL', 'https://checkout-sandbox.payway.com.kh'),
    
    'environment' => env('PAYWAY_ENVIRONMENT', 'sandbox'),
    
    /*
    |--------------------------------------------------------------------------
    | PayWay Settings
    |--------------------------------------------------------------------------
    */
    
    // Default transaction lifetime in minutes (min: 3, max: 43200 = 30 days)
    'default_lifetime' => env('PAYWAY_DEFAULT_LIFETIME', 30),
    
    // Default QR template (template1_color, template2_color, template3_color, etc.)
    'qr_template' => env('PAYWAY_QR_TEMPLATE', 'template3_color'),
    
    // Enable/disable PayWay integration
    'enabled' => env('PAYWAY_ENABLED', true),
    
    // Callback URL for payment notifications
    'callback_url' => env('PAYWAY_CALLBACK_URL', null), // If null, will use route('payway.callback')
    
    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    */
    
    'supported_currencies' => ['USD', 'KHR'],
    
    'min_amount' => [
        'USD' => 0.01,
        'KHR' => 100,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    
    'log_requests' => env('PAYWAY_LOG_REQUESTS', true),
    'log_responses' => env('PAYWAY_LOG_RESPONSES', true),

    /*
    |--------------------------------------------------------------------------
    | ABA Partner API (Online Self-Activation)
    |--------------------------------------------------------------------------
    |
    | Credentials provided by ABA Bank for the partner self-activation API.
    | These allow your platform to register new merchants and inquiry their
    | credential info programmatically.
    |
    */

    'partner_id' => env('ABA_PARTNER_ID', ''),

    'partner_key' => env('ABA_PARTNER_KEY', ''),

    // RSA public key used to encrypt request_data sent to ABA
    'partner_public_key' => env('ABA_PARTNER_PUBLIC_KEY', ''),

    // RSA private key used to decrypt the response data from ABA
    'partner_private_key' => env('ABA_PARTNER_PRIVATE_KEY', ''),

    // 'partner_base_url' => env('ABA_PARTNER_BASE_URL', 'https://merchant.payway.com.kh'),
    'partner_base_url' => env('ABA_PARTNER_BASE_URL', 'https://sandbox.payway.com.kh'),
];
