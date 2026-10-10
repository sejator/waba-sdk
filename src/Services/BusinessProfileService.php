<?php

namespace Sejator\WabaSdk\Services;

class BusinessProfileService
{
    public function __construct(
        protected Client $client,
    ) {}

    /**
     * GET /{phone_number_id}/whatsapp_business_profile. Respons Meta
     * dibungkus edge standar ({"data": [{...}]}) jadi SDK unwrap dulu sebelum return ke consumer.
     */
    public function get(string $phoneNumberId, array $fields = []): array
    {
        if (empty($fields)) {
            $fields = [
                'about',
                'address',
                'description',
                'email',
                'profile_picture_url',
                'websites',
                'vertical',
            ];
        }

        return $this->client->get(
            "{$phoneNumberId}/whatsapp_business_profile",
            [
                'fields' => implode(',', $fields),
            ],
        );
    }

    /**
     * POST /{phone_number_id}/whatsapp_business_profile. $payload bebas
     * (about/address/description/email/websites/vertical/
     * profile_picture_handle) - jadi SDK tidak melakukan validasi payload,
     * biarkan API yang menolak jika ada field yang tidak valid.
     */
    public function update(string $phoneNumberId, array $payload): array
    {
        return $this->client->post(
            "{$phoneNumberId}/whatsapp_business_profile",
            [
                'messaging_product' => 'whatsapp',
                ...$payload,
            ],
        );
    }
}
