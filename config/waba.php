<?php

return [

    'meta' => [

        /*
        |--------------------------------------------------------------------------
        | App Credentials
        |--------------------------------------------------------------------------
        */

        'app_id' => env(
            'META_APP_ID'
        ),

        'app_secret' => env(
            'META_APP_SECRET'
        ),

        /*
        |--------------------------------------------------------------------------
        | Graph API
        |--------------------------------------------------------------------------
        */

        'api_version' => env(
            'META_API_VERSION',
            'v25.0'
        ),

        'base_url' => sprintf(
            'https://graph.facebook.com/%s',
            env('META_API_VERSION', 'v25.0')
        ),

        /*
        |--------------------------------------------------------------------------
        | Default Token
        |--------------------------------------------------------------------------
        */

        'access_token' => env(
            'META_ACCESS_TOKEN'
        ),

        /*
        |--------------------------------------------------------------------------
        | System User Token
        |--------------------------------------------------------------------------
        |
        | Required for:
        | - debug_token
        | - shared WABA API
        | - owned WABA API
        | - management endpoints
        |
        */

        'system_user_token' => env(
            'META_SYSTEM_USER_TOKEN'
        ),

        /*
        |--------------------------------------------------------------------------
        | Business Portfolio
        |--------------------------------------------------------------------------
        */
        'business_id' => env(
            'META_BUSINESS_ID'
        ),

    ],

    /*
    |--------------------------------------------------------------------------
    | Embedded Signup
    |--------------------------------------------------------------------------
    */

    'embedded' => [

        /*
        |--------------------------------------------------------------------------
        | Embedded Signup Config
        |--------------------------------------------------------------------------
        */

        'config_id' => env(
            'META_CONFIGURATION_ID'
        ),

        'redirect_uri' => env(
            'META_REDIRECT_URI'
        ),

        /*
        |--------------------------------------------------------------------------
        | OAuth Version
        |--------------------------------------------------------------------------
        */

        'oauth_version' => env(
            'META_OAUTH_VERSION',
            env('META_API_VERSION', 'v25.0')
        ),

        /*
        |--------------------------------------------------------------------------
        | Embedded Signup Version
        |--------------------------------------------------------------------------
        */

        'version' => env(
            'META_EMBEDDED_VERSION',
            'v4'
        ),

        /*
        |--------------------------------------------------------------------------
        | Auto Resolve WABA
        |--------------------------------------------------------------------------
        |
        | Resolve WABA automatically
        | using /debug_token flow.
        |
        */

        'auto_resolve_waba' => env(
            'META_AUTO_RESOLVE_WABA',
            true
        ),

        /*
        |--------------------------------------------------------------------------
        | Auto Subscribe Webhook
        |--------------------------------------------------------------------------
        */

        'auto_subscribe_webhook' => env(
            'META_AUTO_SUBSCRIBE_WEBHOOK',
            true
        ),

        /*
        |--------------------------------------------------------------------------
        | Auto Fetch Assets
        |--------------------------------------------------------------------------
        */

        'auto_fetch_phone_numbers' => env(
            'META_AUTO_FETCH_PHONES',
            true
        ),

        'auto_fetch_templates' => env(
            'META_AUTO_FETCH_TEMPLATES',
            true
        ),
        
        'state_ttl' => env(
            'META_STATE_TTL',
            300
        ),
    ],

    'http' => [
        'timeout' => env(
            'WABA_HTTP_TIMEOUT',
            30
        ),
        
        'retry' => [
            'times' => env(
                'WABA_HTTP_RETRY_TIMES',
                2
            ),

            'sleep' => env(
                'WABA_HTTP_RETRY_SLEEP',
                500
            ),
        ],
    ],
];
