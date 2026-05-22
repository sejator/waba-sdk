<?php

namespace Sejator\WabaSdk\Support\Embedded;

use Illuminate\Support\Str;

class OAuthUrlGenerator
{
    public function generate(): array
    {
        $this->ensureConfigured();

        $state = Str::uuid()->toString();

        $extras = [
            'sessionInfoVersion' => '3',
            'version' => 'v4',
        ];

        $redirect = config(
            'waba.embedded.redirect_uri'
        );

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
            'extras' => json_encode($extras),
        ]);
        $version = config(
            'waba.embedded.oauth_version',
            'v23.0'
        );

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
