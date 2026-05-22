<?php

namespace Sejator\WabaSdk\Support\Embedded;

use Illuminate\Support\Facades\Http;
use Sejator\WabaSdk\Exceptions\TokenExchangeException;

class TokenExchanger
{
    public function exchange(string $code): array
    {
        $version = config(
            'waba.embedded.oauth_version',
            'v23.0'
        );

        $response = Http::get(
            "https://graph.facebook.com/{$version}/oauth/access_token",
            [
                'client_id' => config('waba.meta.app_id'),
                'client_secret' => config('waba.meta.app_secret'),
                'redirect_uri' => config(
                    'waba.embedded.redirect_uri'
                ),
                'code' => $code,
            ]
        );

        if ($response->failed()) {

            throw new TokenExchangeException(
                $response->body()
            );
        }

        return $response->json();
    }
}
