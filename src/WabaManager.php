<?php

namespace Sejator\WabaSdk;

use RuntimeException;
use Sejator\WabaSdk\Http\WabaClient;
use Sejator\WabaSdk\Endpoints\Media;
use Sejator\WabaSdk\Endpoints\Message;
use Sejator\WabaSdk\Endpoints\Template;
use Sejator\WabaSdk\Endpoints\PhoneNumber;
use Sejator\WabaSdk\Auth\CloudAuth;
use Sejator\WabaSdk\Auth\EmbeddedSignup;
use Sejator\WabaSdk\Billing\CreditManager;
use Sejator\WabaSdk\User\AdminUserManager;
use Sejator\WabaSdk\User\EmbeddedUserManager;
use Sejator\WabaSdk\Webhook\WebhookPayload;
use Sejator\WabaSdk\Webhook\WebhookVerifier;

class WabaManager
{
    protected ?string $accessToken = null;

    public function withAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    protected function client(): WabaClient
    {
        if (!$this->accessToken) {
            throw new RuntimeException(
                'WABA access token not set. Call withAccessToken($token) first.'
            );
        }

        return new WabaClient($this->accessToken);
    }

    /* ---------------- Messaging APIs ---------------- */

    public function messages(string $phoneNumberId): Message
    {
        return new Message($this->client(), $phoneNumberId);
    }

    public function templates(string $wabaId): Template
    {
        return new Template($this->client(), $wabaId);
    }

    public function media(): Media
    {
        return new Media($this->client());
    }

    public function phoneNumbers(): PhoneNumber
    {
        return new PhoneNumber($this->client());
    }

    /* ---------------- Embedded Signup ---------------- */

    public function embeddedSignup(): EmbeddedSignup
    {
        return EmbeddedSignup::make();
    }

    public function exchangeEmbeddedCode(string $code): array
    {
        return app(CloudAuth::class)->exchangeEmbeddedCode($code);
    }

    public function exchangeOAuthCode(string $code, string $redirectUri): array
    {
        return app(CloudAuth::class)->exchangeOAuthCode($code, $redirectUri);
    }

    /* ---------------- Webhook ---------------- */
    public function parseWebhook(string $rawPayload): WebhookPayload
    {
        return new WebhookPayload($rawPayload);
    }

    public function verifyWebhookSignature(
        string $payload,
        string $appSecret,
        ?string $signature,
    ): bool {
        return WebhookVerifier::verify(
            $payload,
            $appSecret,
            $signature
        );
    }

    /* ---------------- User Managers ---------------- */

    /** BSP / Admin setup */
    public function admin(): AdminUserManager
    {
        return new AdminUserManager();
    }

    /** Embedded Signup */
    public function embedded(): EmbeddedUserManager
    {
        return new EmbeddedUserManager($this->client());
    }

    public function credit(): CreditManager
    {
        return new CreditManager($this->client());
    }

    public function mode(): string
    {
        return config('waba.meta.mode');
    }
}
