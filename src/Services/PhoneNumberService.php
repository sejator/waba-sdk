<?php

namespace Sejator\WabaSdk\Services;

class PhoneNumberService
{
    public function __construct(
        protected Client $client,
    ) {}

    public function all(string $wabaId, array $fields = []): array
    {

        if (empty($fields)) {
            $fields = [
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
                'name_status',
                'new_name_status',
                'quality_score',
            ];
        }

        return $this->client->get(
            "{$wabaId}/phone_numbers",
            [
                'fields' => implode(
                    ',',
                    $fields
                ),
            ]
        );
    }

    public function find(string $phoneNumberId, array $fields = []): array
    {
        if (empty($fields)) {
            $fields = [
                'id',
                'display_phone_number',
                'verified_name',
                'status',
                'quality_rating',
                'search_visibility',
                'platform_type',
                'code_verification_status',
                'name_status',
                'new_name_status',
                'quality_score',
            ];
        }

        return $this->client->get(
            $phoneNumberId,
            [
                'fields' => implode(
                    ',',
                    $fields
                ),
            ]
        );
    }

    public function register(string $phoneNumberId, string $pin,): array
    {
        return $this->client->post(
            "{$phoneNumberId}/register",
            [
                'messaging_product' => 'whatsapp',
                'pin' => $pin,
            ]
        );
    }

    public function deregister(string $phoneNumberId): array
    {
        return $this->client->post(
            "{$phoneNumberId}/deregister",
            [
                'messaging_product' => 'whatsapp',
            ]
        );
    }

    public function verify(string $phoneNumberId, string $code): array
    {
        return $this->client->post(
            "{$phoneNumberId}/verify_code",
            [
                'code' => $code,
            ]
        );
    }

    public function requestCode(string $phoneNumberId, string $method = 'SMS', string $language = 'en_US'): array
    {
        return $this->client->post(
            "{$phoneNumberId}/request_code",
            [
                'code_method' => $method,
                'language' => $language,
            ]
        );
    }

    public function health(string $phoneNumberId): array
    {
        return $this->client->get(
            $phoneNumberId,
            [
                'fields' => implode(',', [
                    'status',
                    'quality_rating',
                    'quality_score',
                ]),
            ]
        );
    }
}
