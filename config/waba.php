<?php

return [
    'url' => env('WABA_URL', ''),
    'token' => env('WABA_TOKEN'),
    'otp_expiry' => env('WABA_OTP_EXPIRY', 5), // in minutes
    'internal' => [
        'key' => env('WABA_INTERNAL_KEY', ''),
        'gateway_url' => env('WABA_GATEWAY_URL', ''),
    ],
    'meta' => [
        'base_url' => env('META_BASE_URL', 'https://graph.facebook.com/v23.0'),
        'access_token' => env('META_ACCESS_TOKEN', ''),
    ]
];
