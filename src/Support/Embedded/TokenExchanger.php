<?php

namespace Sejator\WabaSdk\Support\Embedded;

use Illuminate\Support\Facades\Http;

use Sejator\WabaSdk\Exceptions\TokenExchangeException;

class TokenExchanger
{
    /*
    |--------------------------------------------------------------------------
    | Exchange Authorization Code
    |--------------------------------------------------------------------------
    |
    */

    public function exchange(string $code, ?string $redirectUri = null,): array
    {
        $grapUrl = config(
            'waba.meta.base_url',
            'https://graph.facebook.com/v25.0'
        );

        $redirectUri ??= config(
            'waba.embedded.redirect_uri'
        );

        $response = Http::asForm()
            ->acceptJson()
            ->post(
                "{$grapUrl}/oauth/access_token",
                [
                    'client_id' => config(
                        'waba.meta.app_id'
                    ),
                    'client_secret' => config(
                        'waba.meta.app_secret'
                    ),
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirectUri,
                ]
            );

        if ($response->failed()) {
            throw new TokenExchangeException(
                data_get(
                    $response->json(),
                    'error.message',
                    $response->body()
                )

            );
        }

        $payload = $response->json();

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
                'token_type'
            ),
            'payload' => $payload,
        ];
    }
}
