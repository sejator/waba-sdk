<?php

namespace Sejator\WabaSdk\Services;

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
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Generate Embedded Signup URL
    |--------------------------------------------------------------------------
    */

    public function signupUrl(
        ?string $state = null
    ): array {

        return $this->urlGenerator
            ->generate($state);
    }

    /*
    |--------------------------------------------------------------------------
    | Exchange Authorization Code
    |--------------------------------------------------------------------------
    */

    public function exchangeCode(
        string $code
    ): array {

        return $this->tokenExchanger
            ->exchange($code);
    }

    /*
    |--------------------------------------------------------------------------
    | Provision Embedded Assets
    |--------------------------------------------------------------------------
    |
    | Official Meta Flow:
    |
    | 1. Fetch WABA
    | 2. Fetch Phones
    | 3. Subscribe App
    |
    */

    public function provision(
        string $accessToken,
        ?string $wabaId = null,
    ): EmbeddedSignupResult {

        /*
        |--------------------------------------------------------------------------
        | Graph Client
        |--------------------------------------------------------------------------
        */

        $graph = $this->client
            ->withToken($accessToken);

        /*
        |--------------------------------------------------------------------------
        | Override Services
        |--------------------------------------------------------------------------
        */

        $businesses = new BusinessService(
            $graph
        );

        $phones = new PhoneNumberService(
            $graph
        );

        /*
        |--------------------------------------------------------------------------
        | Resolve WABA ID
        |--------------------------------------------------------------------------
        |
        | Optional:
        | You may pass explicit WABA ID.
        |
        */

        if (!$wabaId) {

            throw new \RuntimeException(
                'WABA ID is required for provisioning.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Fetch WABA
        |--------------------------------------------------------------------------
        */

        $waba = $businesses->waba(
            $wabaId
        );

        /*
        |--------------------------------------------------------------------------
        | Fetch Phone Numbers
        |--------------------------------------------------------------------------
        */

        $phoneResponse = $phones->all(
            $wabaId
        );

        $phone = collect(
            data_get(
                $phoneResponse,
                'data',
                []
            )
        )->first();

        /*
        |--------------------------------------------------------------------------
        | Subscribe App
        |--------------------------------------------------------------------------
        */

        $this->subscribeWebhook(
            $wabaId,
            $accessToken
        );

        /*
        |--------------------------------------------------------------------------
        | Result DTO
        |--------------------------------------------------------------------------
        */

        return EmbeddedSignupResult::fromArray([

            /*
            |--------------------------------------------------------------------------
            | Token
            |--------------------------------------------------------------------------
            */

            'access_token' =>
            $accessToken,

            /*
            |--------------------------------------------------------------------------
            | Business
            |--------------------------------------------------------------------------
            */

            'business_id' => data_get(
                $waba,
                'owner_business_info.id'
            ),

            'business_name' => data_get(
                $waba,
                'owner_business_info.name'
            ),

            /*
            |--------------------------------------------------------------------------
            | WABA
            |--------------------------------------------------------------------------
            */

            'waba_id' => data_get(
                $waba,
                'id'
            ),

            /*
            |--------------------------------------------------------------------------
            | Phone
            |--------------------------------------------------------------------------
            */

            'phone_number_id' => data_get(
                $phone,
                'id'
            ),

            'display_phone_number' => data_get(
                $phone,
                'display_phone_number'
            ),

            /*
            |--------------------------------------------------------------------------
            | Phones
            |--------------------------------------------------------------------------
            */

            'phone_numbers' => data_get(
                $phoneResponse,
                'data',
                []
            ),

            /*
            |--------------------------------------------------------------------------
            | Templates
            |--------------------------------------------------------------------------
            */

            'templates' => data_get(
                $templateResponse,
                'data',
                []
            ),

            /*
            |--------------------------------------------------------------------------
            | Payload
            |--------------------------------------------------------------------------
            */

            'payload' => [

                'waba' =>
                $waba,

                'phones' =>
                $phoneResponse,

                'templates' =>
                $templateResponse,

            ],

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Subscribe App To WABA
    |--------------------------------------------------------------------------
    */

    public function subscribeWebhook(
        string $wabaId,
        ?string $accessToken = null,
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Dynamic Token
        |--------------------------------------------------------------------------
        */

        if ($accessToken) {

            $client = $this->client
                ->withToken($accessToken);

            $subscriber =
                new WebhookSubscriber(
                    $client
                );

            return $subscriber
                ->subscribe($wabaId);
        }

        return $this->subscriber
            ->subscribe($wabaId);
    }
}
