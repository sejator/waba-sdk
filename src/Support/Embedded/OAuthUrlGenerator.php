<?php

namespace Sejator\WabaSdk\Support\Embedded;

use Illuminate\Support\Str;

class OAuthUrlGenerator
{
    public function generate(): array
    {
        $this->ensureConfigured();

        $state = Str::uuid()->toString();

        $version = config(
            'waba.embedded.oauth_version',
            'v23.0'
        );

        $query = http_build_query([
            'client_id' => config('waba.meta.app_id'),
            'redirect_uri' => config(
                'waba.embedded.redirect_uri'
            ),

            'scope' => 'business_management,whatsapp_business_management,whatsapp_business_messaging',
            'response_type' => 'code',
            'state' => $state,
            'config_id' => config(
                'waba.embedded.config_id'
            ),
        ]);

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
