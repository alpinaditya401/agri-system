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
    ],

];
