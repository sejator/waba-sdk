<?php

namespace Sejator\WabaSdk\Webhook;

class WebhookVerifier
{
    /**
     * Memverifikasi signature webhook Meta (X-Hub-Signature-256).
     *
     * Signature dihitung menggunakan HMAC SHA256
     * dari payload mentah dan App Secret Meta.
     *
     * @param string      $payload   Raw request body
     * @param string      $appSecret Meta App Secret
     * @param string|null $signature Header X-Hub-Signature-256
     *
     * @return bool
     */
    public static function verify(
        string $payload,
        string $appSecret,
        ?string $signature,
    ): bool {
        if (
            empty($payload) ||
            empty($signature) ||
            empty($appSecret) ||
            !str_starts_with($signature, 'sha256=')
        ) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac(
            'sha256',
            $payload,
            $appSecret
        );

        return hash_equals($expected, $signature);
    }
}
