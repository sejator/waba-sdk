<?php

namespace Sejator\WabaSdk\Support\Embedded;

use Illuminate\Support\Str;
use Sejator\WabaSdk\Exceptions\EmbeddedSignupException;

class OAuthUrlGenerator
{
    public function generate(?string $state = null, array $scopes = []): array
    {

        $this->ensureConfigured();

        $state ??= Str::uuid()->toString();

        $redirectUri = config(
            'waba.embedded.redirect_uri'
        );

        if (empty($scopes)) {
            $scopes = [
                'whatsapp_business_management',
                'whatsapp_business_messaging',
            ];
        }

        $extras = [
            'version' => config(
                'waba.embedded.version',
                'v4'
            ),
        ];

        $query = [
            'display' => 'popup',
            'client_id' => config(
                'waba.meta.app_id'
            ),
            'redirect_uri' => $redirectUri,
            'fallback_redirect_uri' => $redirectUri,
            'config_id' => config(
                'waba.embedded.config_id'
            ),
            'response_type' => 'code',
            'override_default_response_type' => 'true',
            'scope' => implode(
                ',',
                $scopes
            ),
            'state' => $state,
            'extras' => json_encode(
                $extras,
                JSON_UNESCAPED_SLASHES
            ),

        ];

        $version = config(
            'waba.embedded.oauth_version',
            config(
                'waba.meta.api_version',
                'v25.0'
            )
        );

        return [
            'state' => $state,
            'url' => sprintf(
                'https://www.facebook.com/%s/dialog/oauth?%s',
                $version,
                http_build_query($query)
            ),
            'metadata' => [
                'version' => config(
                    'waba.embedded.version'
                ),
                'scopes' => $scopes,
            ],
        ];
    }

    protected function ensureConfigured(): void
    {
        if (!config('waba.meta.app_id')) {

            throw new EmbeddedSignupException(
                'META_APP_ID is not configured.'
            );
        }

        if (!config('waba.embedded.config_id')) {

            throw new EmbeddedSignupException(
                'META_CONFIGURATION_ID is not configured.'
            );
        }

        if (!config('waba.embedded.redirect_uri')) {

            throw new EmbeddedSignupException(
                'META_REDIRECT_URI is not configured.'
            );
        }
    }
}
