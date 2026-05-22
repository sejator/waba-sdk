<?php

namespace Sejator\WabaSdk\Services;

class PhoneNumberService
{
    public function __construct(
        protected Client $client,
    ) {}

    public function all(string $wabaId): array
    {
        return $this->client->get($wabaId . '/phone_numbers');
    }

    public function verify(string $phoneNumberId, string $code): array
    {
        return $this->client->post($phoneNumberId . '/verify_code', [
            'code' => $code,
        ]);
    }
}
