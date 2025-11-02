<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSLCommerz Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for SSLCommerz payment gateway
    | integration. Make sure to set the correct values in your .env file.
    |
    */

    'store_id' => env('SSLCOMMERZ_STORE_ID'),
    'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
    'is_sandbox' => env('SSLCOMMERZ_IS_SANDBOX', true),
    
    // API URLs
    'sandbox_url' => 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php',
    'live_url' => 'https://securepay.sslcommerz.com/gwprocess/v4/api.php',
    
    // Validation URLs
    'sandbox_validation_url' => 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php',
    'live_validation_url' => 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php',
    
    // Default currency
    'currency' => 'BDT',
    
    // Success/Fail/Cancel URLs (will be overridden by route URLs)
    'success_url' => env('APP_URL') . '/api/payment/success',
    'fail_url' => env('APP_URL') . '/api/payment/fail',
    'cancel_url' => env('APP_URL') . '/api/payment/cancel',
    'ipn_url' => env('APP_URL') . '/api/payment/ipn',
];