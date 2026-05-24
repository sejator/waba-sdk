<?php

namespace Sejator\WabaSdk\Services;

use RuntimeException;
use Sejator\WabaSdk\DTO\EmbeddedSignupResult;
use Sejator\WabaSdk\Support\ComponentNormalizer;
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
        protected TemplateService $templates,
        protected WabaResolverService $resolver,
        protected ComponentNormalizer $normalizer,
    ) {}

    public function signupUrl(?string $state = null, array $scopes = []): array
    {

        return $this->urlGenerator
            ->generate(
                state: $state,
                scopes: $scopes,
            );
    }

    public function exchangeCode(string $code, ?string $redirectUri = null): array
    {
        return $this->tokenExchanger
            ->exchange(
                code: $code,
                redirectUri: $redirectUri,
            );
    }

    /**
     * Provision Embedded Assets
     * Official Meta Flow:
     * 
     * 1. Resolve WABA
     * 2. Fetch WABA
     * 3. Fetch Phones
     * 4. Fetch Templates
     * 5. Subscribe App
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

        $graph = $this->client
            ->withToken($accessToken);

        $businesses = new BusinessService(
            $graph
        );

        $phones = new PhoneNumberService(
            $graph
        );

        $templates = new TemplateService(
            $graph,
            $this->normalizer
        );

        $waba = $businesses->waba($wabaId);

        $phoneResponse = [];

        if (config('waba.embedded.auto_fetch_phone_numbers', true)) {
            $phoneResponse = $phones
                ->all($wabaId);
        }

        $templateResponse = [];

        if (config('waba.embedded.auto_fetch_templates', true)) {
            $templateResponse = $templates
                ->all($wabaId);
        }

        $phone = collect(
            data_get($phoneResponse, 'data', [])
        )->first();

        if (config('waba.embedded.auto_subscribe_webhook', true)) {
            $this->subscribeWebhook(
                $wabaId,
                $accessToken
            );
        }

        return EmbeddedSignupResult::fromArray([
            'access_token' => $accessToken,
            'waba_id' => data_get(
                $waba,
                'id'
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
            'phone_numbers' => data_get(
                $phoneResponse,
                'data',
                []
            ),
            'templates' => data_get(
                $templateResponse,
                'data',
                []
            ),
            'metadata' => [
                'embedded_version' => config(
                    'waba.embedded.version'
                ),
                'api_version' => config(
                    'waba.meta.api_version'
                ),
                'resolved_waba' => true,
            ],
        ]);
    }

    public function subscribeWebhook(string $wabaId, ?string $accessToken = null): array
    {
        $client = $accessToken ? $this->client->withToken($accessToken)
            : $this->client
            ->system();

        $subscriber = new WebhookSubscriber(
            $client
        );

        return $subscriber
            ->subscribe($wabaId);
    }
}
