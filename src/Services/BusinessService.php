<?php

namespace Sejator\WabaSdk\Services;

class BusinessService
{
    public function __construct(
        protected Client $client,
    ) {}

    public function me(): array
    {
        return $this->client->get('me');
    }

    public function wabas(string $businessId): array
    {
        return $this->client->get($businessId . '/owned_whatsapp_business_accounts');
    }
}
