<?php
// config/services.php — add this block to your existing services config

return [

    // ... existing services (mail, stripe, etc.)

    /*
    |--------------------------------------------------------------------------
    | BPS (Badan Pusat Statistik) API
    | Docs: https://webapi.bps.go.id/documentation/
    |--------------------------------------------------------------------------
    */
    'bps' => [
        'base_url' => env('BPS_API_BASE_URL', 'https://webapi.bps.go.id/v1/api'),
        'api_key'  => env('BPS_API_KEY', ''),
        'domain'   => env('BPS_API_DOMAIN', '0000'),
        'timeout'  => env('BPS_API_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway (e.g. Midtrans — popular in Indonesia)
    |--------------------------------------------------------------------------
    */
    'midtrans' => [
        'server_key'    => env('MIDTRANS_SERVER_KEY'),
        'client_key'    => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized'  => true,
        'is_3ds'        => true,
        'snap_url'      => env(
            'MIDTRANS_SNAP_URL',
            env('MIDTRANS_IS_PRODUCTION', false)
                ? 'https://app.midtrans.com'
                : 'https://app.sandbox.midtrans.com'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Webhook Verification
    |--------------------------------------------------------------------------
    | Supported gateways: midtrans, xendit, auto.
    | Keep these secrets in .env only. Webhooks are rejected when the selected
    | gateway secret is empty.
    */
    'payment' => [
        'gateway' => env('PAYMENT_GATEWAY', 'midtrans'),
        'webhook_gateway' => env('PAYMENT_WEBHOOK_GATEWAY', 'midtrans'),
    ],

    'xendit' => [
        'callback_token' => env('XENDIT_CALLBACK_TOKEN'),
    ],

];
