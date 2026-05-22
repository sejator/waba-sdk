<?php

namespace Sejator\WabaSdk\Services;

class BusinessService
{
    public function __construct(
        protected Client $client,
    ) {}

    public function waba(string $wabaId): array
    {

        return $this->client->get(
            $wabaId,
            [
                'fields' => implode(',', [
                    'id',
                    'name',
                    'currency',
                    'owner_business_info',
                ]),
            ]
        );
    }
}
