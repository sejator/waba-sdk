<?php

namespace Sejator\WabaSdk\Endpoints;

use Sejator\WabaSdk\Http\WabaClient;

class PhoneNumber
{
    public function __construct(
        protected WabaClient $client,
    ) {}

    /**
     * List phone numbers in WABA
     */
    public function list(string $wabaId): array
    {
        return $this->client->get("{$wabaId}/phone_numbers");
    }

    /**
     * Register phone number (PIN verification)
     */
    public function register(string $phoneNumberId, string $pin): array
    {
        return $this->client->post("{$phoneNumberId}/register", [
            'messaging_product' => 'whatsapp',
            'pin' => $pin,
        ]);
    }

    /**
     * Deregister phone number
     */
    public function deregister(string $phoneNumberId): array
    {
        return $this->client->post("{$phoneNumberId}/deregister", [
            'messaging_product' => 'whatsapp',
        ]);
    }
}
