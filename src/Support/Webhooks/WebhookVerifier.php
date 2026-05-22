<?php

namespace Sejator\WabaSdk\Support\Webhooks;

class WebhookVerifier
{
    public function verify(
        ?string $mode,
        ?string $token,
        ?string $challenge
    ): ?string {

        /*
        |--------------------------------------------------------------------------
        | Validate Subscribe Mode
        |--------------------------------------------------------------------------
        */

        if ($mode !== 'subscribe') {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Verify Token
        |--------------------------------------------------------------------------
        */

        if (
            $token !== config(
                'waba.webhook.verify_token'
            )
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Return Challenge
        |--------------------------------------------------------------------------
        */

        return $challenge;
    }

    public function isValid(
        ?string $mode,
        ?string $token
    ): bool {

        return $mode === 'subscribe'
            && $token === config(
                'waba.webhook.verify_token'
            );
    }
}
