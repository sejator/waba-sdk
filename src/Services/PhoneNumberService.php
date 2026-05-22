<?php

namespace Sejator\WabaSdk\Services;

class PhoneNumberService
{
    public function __construct(
        protected Client $client,
    ) {}

    public function all(string $wabaId): array
    {
        return $this->client->get(
            $wabaId . '/phone_numbers',
            [
                'fields' => implode(',', [
                    'id',
                    'cc',
                    'country_dial_code',
                    'display_phone_number',
                    'verified_name',
                    'status',
                    'quality_rating',
                    'search_visibility',
                    'platform_type',
                    'code_verification_status',

                ]),

            ]

        );
    }

    public function find(string $phoneNumberId): array
    {

        return $this->client->get(
            $phoneNumberId,
            [
                'fields' => implode(',', [
                    'id',
                    'display_phone_number',
                    'verified_name',
                    'status',
                    'quality_rating',
                    'search_visibility',
                    'platform_type',
                    'code_verification_status',
                ]),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Register Phone Number
    |--------------------------------------------------------------------------
    |
    | Required for Cloud API messaging.
    |
    */

    public function register(string $phoneNumberId, string $pin): array
    {

        return $this->client->post(
            $phoneNumberId . '/register',
            [
                'messaging_product' =>
                'whatsapp',
                'pin' => $pin,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Deregister Phone Number
    |--------------------------------------------------------------------------
    */

    public function deregister(string $phoneNumberId): array
    {

        return $this->client->post(
            $phoneNumberId . '/deregister',
            [
                'messaging_product' =>
                'whatsapp',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Verify Phone Number
    |--------------------------------------------------------------------------
    */

    public function verify(string $phoneNumberId, string $code): array
    {
        return $this->client->post(
            $phoneNumberId .
                '/verify_code',
            [
                'code' => $code,
            ]
        );
    }
}
