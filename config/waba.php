<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Meta / Facebook App (WhatsApp Cloud API)
    |--------------------------------------------------------------------------
    |
    | Digunakan untuk:
    | - Manual OAuth (Embedded Signup tanpa FB JS SDK)
    | - Exchange code → access token
    | - WhatsApp Cloud API
    |
    */

    'meta' => [

        /*
        |--------------------------------------------------------------------------
        | Meta App Credentials
        |--------------------------------------------------------------------------
        */
        'app_id'     => env('META_APP_ID'),
        'app_secret' => env('META_APP_SECRET'),
        'system_user_id' => env('META_SYSTEM_USER_ID'),
        'system_user_token' => env('META_SYSTEM_USER_TOKEN'),

        /*
        |--------------------------------------------------------------------------
        | Graph API
        |--------------------------------------------------------------------------
        |
        | Digunakan untuk:
        | - OAuth access_token
        | - debug_token
        | - Cloud API (messages, templates, phone numbers)
        |
        */
        'graph' => [
            'base_url' => env('META_GRAPH_URL', 'https://graph.facebook.com'),
            'version'  => env('META_GRAPH_VERSION', 'v24.0'),
        ],

        /*
        |--------------------------------------------------------------------------
        | OAuth Login (Manual Flow – Facebook Login)
        |--------------------------------------------------------------------------
        |
        | Dipakai untuk:
        | - Build URL dialog OAuth (dialog/oauth)
        | - Redirect callback
        |
        | Flow:
        |   frontend → dialog/oauth → redirect_uri → exchange code
        |
        */
        'oauth' => [

            // Base domain Facebook OAuth
            // Akan dipakai untuk:
            // https://www.facebook.com/v24.0/dialog/oauth
            'base_url' => env(
                'META_OAUTH_BASE_URL',
                'https://www.facebook.com'
            ),

            // OAuth API version
            'version' => env('META_OAUTH_VERSION', 'v24.0'),
            'config_id' => env('META_EMBEDDED_CONFIG_ID'),

            // HARUS sama persis dengan:
            // - yang dipakai saat build dialog/oauth
            // - yang dipakai saat exchange code
            // - yang terdaftar di Meta App Dashboard
            'redirect_uri' => env('META_OAUTH_REDIRECT_URI'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Webhook (WhatsApp Business API)
        |--------------------------------------------------------------------------
        |
        | Endpoint:
        | - GET  /api/waba        (verify)
        | - POST /api/waba        (events)
        |
        */
        'webhook' => [
            'enabled'      => true,
            'url'          => env('META_WEBHOOK_URL'),
            'verify_token' => env('META_WEBHOOK_VERIFY_TOKEN'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Application Mode
        |--------------------------------------------------------------------------
        |
        | development | production
        |
        */
        'mode' => env('META_APP_MODE', 'production'),
    ],

];
