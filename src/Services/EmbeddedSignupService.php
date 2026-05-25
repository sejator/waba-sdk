<?php

namespace Sejator\WabaSdk\Services;

use RuntimeException;
use Sejator\WabaSdk\DTO\EmbeddedSignupResult;
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
        protected BusinessService $businesses,
        protected PhoneNumberService $phones,
        protected WabaResolverService $resolver,
    ) {}

    public function signupUrl(?string $state = null, array $scopes = []): array
    {

        return $this->urlGenerator
            ->generate(
                state: $state,
                scopes: $scopes,
            );
    }

    public function exchangeCode(string $code, ?string $redirectUri = null,): array
    {
        return $this->tokenExchanger
            ->exchange(
                code: $code,
                redirectUri: $redirectUri,
            );
    }

    /**
     * Complete Embedded Signup
     *
     * Flow:
     *
     * 1. Exchange OAuth Code
     * 2. Resolve WABA
     * 3. Fetch WABA
     * 4. Fetch Phones
     * 5. Subscribe Webhook
     *
     */
    public function completeSignup(string $code, string $state): EmbeddedSignupResult
    {
        $token = $this->exchangeCode(
            $code
        );

        $accessToken = data_get(
            $token,
            'access_token'
        );

        if (!$accessToken) {

            throw new RuntimeException(
                'Meta access token not returned.'
            );
        }

        $result = $this->provision(
            $accessToken
        );

        return EmbeddedSignupResult::fromArray([
            ...$result->toArray(),
            'status' => 'completed',
            'payload' => [
                ...$result->payload,
                'oauth' => $token,
                'state' => $state,

            ],

        ]);
    }

    /**
     * Provision Embedded Assets
     *
     * Official Meta Flow:
     *
     * 1. Resolve WABA
     * 2. Fetch WABA
     * 3. Fetch Phones
     * 4. Subscribe App
     *
     */

    public function provision(string $accessToken, ?string $wabaId = null): EmbeddedSignupResult
    {
        if (!$wabaId && config('waba.embedded.auto_resolve_waba', true)) {
            $wabaId = $this->resolver
                ->resolveFromAccessToken(
                    $accessToken
                );
        }

        if (!$wabaId) {
            throw new RuntimeException(
                'Unable to resolve WABA ID.'
            );
        }

        $businesses = $this->businesses(
            $accessToken
        );

        $phones = $this->phones(
            $accessToken
        );

        $waba = $businesses
            ->waba($wabaId);

        $phoneResponse = [];

        if (config('waba.embedded.auto_fetch_phone_numbers', true)) {
            $phoneResponse = $phones
                ->all($wabaId);
        }

        $phone = collect(
            data_get(
                $phoneResponse,
                'data',
                []
            )
        )->first();

        $subscription = null;
        if (config('waba.embedded.auto_subscribe_webhook', true)) {
            $subscription = $this->subscribeWebhook(
                $wabaId,
                $accessToken
            );
        }

        $registration = null;

        if (config('waba.embedded.auto_register_phone', true) && data_get($phone, 'id')) {
            $registration = $this->registerPhone(
                $phones,
                $phone
            );
        }

        return EmbeddedSignupResult::fromArray([
            'access_token' => $accessToken,
            'waba_id' => data_get(
                $waba,
                'id'
            ),
            'waba_name' => data_get(
                $waba,
                'name'
            ),
            'business_id' => data_get(
                $waba,
                'owner_business_info.id'
            ),
            'business_name' => data_get(
                $waba,
                'owner_business_info.name'
            ),
            'phone_number_id' => data_get(
                $phone,
                'id'
            ),
            'display_phone_number' => data_get(
                $phone,
                'display_phone_number'
            ),
            'webhook_subscription' => $subscription,
            'phone_registration' => $registration,

            'payload' => [
                'waba' => $waba,
                'phone_numbers' => data_get(
                    $phoneResponse,
                    'data',
                    []
                ),
                'webhook_subscription' => $subscription,
                'phone_registration' => $registration,
                'metadata' => [
                    'embedded_version' => config(
                        'waba.embedded.version'
                    ),
                    'api_version' => config(
                        'waba.meta.api_version'
                    ),
                    'resolved_waba' => true,
                ],

            ],

        ]);
    }

    public function subscribeWebhook(string $wabaId, ?string $accessToken = null): array
    {
        $client = $accessToken
            ? $this->client
            ->withToken($accessToken)

            : $this->client
            ->system();

        $subscriber = new WebhookSubscriber(
            $client
        );

        return $subscriber
            ->subscribe($wabaId);
    }

    protected function graph(string $accessToken): Client
    {
        return $this->client
            ->withToken(
                $accessToken
            );
    }

    protected function businesses(string $accessToken): BusinessService
    {
        return new BusinessService(
            $this->graph(
                $accessToken
            )
        );
    }

    protected function phones(string $accessToken): PhoneNumberService
    {
        return new PhoneNumberService(
            $this->graph(
                $accessToken
            )
        );
    }

    protected function registerPhone(PhoneNumberService $phones, array $phone): array
    {
        $phoneId = data_get(
            $phone,
            'id'
        );

        if (!$phoneId) {
            return [
                'success' => false,
                'message' => 'Phone number ID missing.',
            ];
        }

        if (data_get($phone, 'code_verification_status') === 'VERIFIED') {
            return [
                'success' => true,
                'skipped' => true,
                'message' => 'Phone already verified.',
            ];
        }

        try {
            $response = $phones->register(
                $phoneId,
                config('waba.embedded.default_pin', '123456')
            );

            return [
                'success' => true,
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
