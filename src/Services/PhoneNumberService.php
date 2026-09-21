<?php

namespace Sejator\WabaSdk\Services;

use Sejator\WabaSdk\Exceptions\WabaException;

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

    public function updateSettings(string $phoneNumberId, array $settings): array
    {
        return $this->client->post(
            "{$phoneNumberId}/settings",
            $settings,
        );
    }

    /**
     * Status Official Business Account (OBA) nomor ini, dinormalisasi ke
     * ['oba_status' => ?string, 'status_message' => ?string]. Dokumentasi
     * Meta memberi dua bentuk respons (edge /official_business_account
     * dengan oba_status di root, atau field pada node nomor dibungkus di
     * official_business_account) - edge dicoba dulu, fallback ke node.
     */
    public function officialBusinessAccount(string $phoneNumberId): array
    {
        try {
            $response = $this->client->get(
                "{$phoneNumberId}/official_business_account",
                ['fields' => 'oba_status,status_message'],
            );
        } catch (WabaException) {
            $response = $this->client->get(
                $phoneNumberId,
                ['fields' => 'official_business_account'],
            );
        }

        return [
            'oba_status' => data_get($response, 'oba_status')
                ?? data_get($response, 'official_business_account.oba_status'),
            'status_message' => data_get($response, 'status_message')
                ?? data_get($response, 'official_business_account.status_message'),
        ];
    }

    /**
     * Ajukan OBA. Field: business_website_url & primary_country_of_operation
     * (wajib), primary_language, parent_business_or_brand, supporting_links
     * (5-10 URL), additional_supporting_information (opsional). Respons
     * Meta: success, message, updated_status, tracking_id.
     */
    public function submitOfficialBusinessAccount(string $phoneNumberId, array $payload): array
    {
        return $this->client->post(
            "{$phoneNumberId}/official_business_account",
            $payload,
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
