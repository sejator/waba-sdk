<?php

namespace Sejator\WabaSdk\Services;

use Sejator\WabaSdk\Support\Embedded\OAuthUrlGenerator;
use Sejator\WabaSdk\Support\Embedded\TokenExchanger;
use Sejator\WabaSdk\Support\Embedded\WebhookSubscriber;

class EmbeddedSignupService
{
    public function __construct(
        protected Client $client,
        protected OAuthUrlGenerator $urlGenerator,
        protected TokenExchanger $tokenExchanger,
        protected WebhookSubscriber $subscriber,
    ) {}

    public function signupUrl(
        ?string $state = null
    ): array {

        return $this->urlGenerator
            ->generate($state);
    }

    public function exchangeCode(
        string $code
    ): array {

        return $this->tokenExchanger
            ->exchange($code);
    }

    public function subscribeWebhook(
        string $wabaId
    ): array {

        return $this->subscriber
            ->subscribe($wabaId);
    }
}
