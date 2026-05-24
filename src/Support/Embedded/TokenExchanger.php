<?php

namespace Sejator\WabaSdk\Support\Embedded;

use Sejator\WabaSdk\Exceptions\TokenExchangeException;
use Sejator\WabaSdk\Services\Client;

class TokenExchanger
{
    public function __construct(
        protected Client $client,
    ) {}

    /**
     * Exchange Authorization Code
     *
     * Official Meta OAuth Flow
     *
     */
    public function exchange(string $code, ?string $redirectUri = null): array
    {
        $this->ensureConfigured();

        $redirectUri ??= config(
            'waba.embedded.redirect_uri'
        );

        try {
            $payload = $this->client
                ->get('/oauth/access_token', [
                    'client_id' => config(
                        'waba.meta.app_id'
                    ),
                    'client_secret' => config(
                        'waba.meta.app_secret'
                    ),
                    'redirect_uri' => $redirectUri,
                    'code' => $code,
                ]);

            if (!data_get($payload, 'access_token')) {

                throw new TokenExchangeException(
                    'Meta access token not returned.'
                );
            }

            return [
                'access_token' => data_get(
                    $payload,
                    'access_token'
                ),
                'token_type' => data_get(
                    $payload,
                    'token_type',
                    'bearer'
                ),
                'expires_in' => data_get(
                    $payload,
                    'expires_in'
                ),
                'payload' => $payload,
            ];
        } catch (\Throwable $e) {
            if ($e instanceof TokenExchangeException) {
                throw $e;
            }

            throw new TokenExchangeException(
                $e->getMessage(),
            );
        }
    }

    protected function ensureConfigured(): void
    {
        if (!config('waba.meta.app_id')) {

            throw new TokenExchangeException(
                'META_APP_ID is not configured.'
            );
        }

        if (!config('waba.meta.app_secret')) {

            throw new TokenExchangeException(
                'META_APP_SECRET is not configured.'
            );
        }

        if (!config('waba.embedded.redirect_uri')) {

            throw new TokenExchangeException(
                'META_REDIRECT_URI is not configured.'
            );
        }
    }
}
