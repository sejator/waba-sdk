<?php

namespace Sejator\WabaSdk\Support\Embedded;

use Illuminate\Support\Str;

class OAuthUrlGenerator
{
    public function generate(?string $state = null): array
    {

        $this->ensureConfigured();

        /*
        |--------------------------------------------------------------------------
        | OAuth State
        |--------------------------------------------------------------------------
        */

        $state ??= Str::uuid()->toString();

        /*
        |--------------------------------------------------------------------------
        | Embedded Extras
        |--------------------------------------------------------------------------
        */

        $extras = [
            'sessionInfoVersion' => '3',
            'version' => 'v4',
        ];

        /*
        |--------------------------------------------------------------------------
        | Redirect URI
        |--------------------------------------------------------------------------
        */

        $redirect = config(
            'waba.embedded.redirect_uri'
        );

        /*
        |--------------------------------------------------------------------------
        | Query
        |--------------------------------------------------------------------------
        */

        $query = http_build_query([
            'display' => 'popup',

            'client_id' => config(
                'waba.meta.app_id'
            ),

            'redirect_uri' => $redirect,

            'fallback_redirect_uri' => $redirect,

            'config_id' => config(
                'waba.embedded.config_id'
            ),

            'response_type' => 'code',

            'override_default_response_type' => true,

            'state' => $state,

            /*
            |--------------------------------------------------------------------------
            | Embedded Extras
            |--------------------------------------------------------------------------
            */

            'extras' => json_encode(
                $extras
            ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | OAuth Version
        |--------------------------------------------------------------------------
        */

        $version = config(
            'waba.embedded.oauth_version',
            'v23.0'
        );

        /*
        |--------------------------------------------------------------------------
        | Final URL
        |--------------------------------------------------------------------------
        */

        return [
            'state' => $state,
            'url' =>
            "https://www.facebook.com/{$version}/dialog/oauth?{$query}",
        ];
    }

    protected function ensureConfigured(): void
    {
        if (!config('waba.meta.app_id')) {

            throw new \RuntimeException(
                'META_APP_ID is not configured.'
            );
        }

        if (!config('waba.embedded.config_id')) {

            throw new \RuntimeException(
                'META_CONFIGURATION_ID is not configured.'
            );
        }

        if (!config('waba.embedded.redirect_uri')) {

            throw new \RuntimeException(
                'META_REDIRECT_URI is not configured.'
            );
        }
    }
}
