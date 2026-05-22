<?php

namespace Sejator\WabaSdk\Support\Webhooks;

use Illuminate\Http\Request;

class WebhookSignatureValidator
{
    public function validate(
        Request $request
    ): bool {

        $signature = $request->header(
            'X-Hub-Signature-256'
        );

        if (!$signature) {
            return false;
        }

        $appSecret = config(
            'waba.meta.app_secret'
        );

        if (!$appSecret) {
            return false;
        }

        $payload = $request->getContent();

        $expected = 'sha256=' . hash_hmac(
            'sha256',
            $payload,
            $appSecret
        );

        return hash_equals(
            $expected,
            $signature
        );
    }
}
