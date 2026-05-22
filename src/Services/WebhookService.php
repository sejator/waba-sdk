<?php

namespace Sejator\WabaSdk\Services;

use Sejator\WabaSdk\Support\Webhooks\WebhookVerifier;

class WebhookService
{
    public function __construct(
        protected WebhookVerifier $verifier,
    ) {}

    public function verify(string $mode, string $token, string $challenge): ?string
    {
        return $this->verifier->verify($mode, $token, $challenge);
    }
}
