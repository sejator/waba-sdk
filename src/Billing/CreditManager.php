<?php

namespace Sejator\WabaSdk\Billing;

use Sejator\WabaSdk\Http\WabaClient;

class CreditManager
{
    public function __construct(
        protected WabaClient $client
    ) {}

    public function listCreditLines(string $businessId): array
    {
        return $this->client->get(
            "{$businessId}/extendedcredits",
            ['fields' => 'id,legal_entity_name']
        );
    }

    public function attachCreditLine(
        string $creditLineId,
        string $wabaId,
        string $currency
    ): array {
        return $this->client->post(
            "{$creditLineId}/whatsapp_credit_sharing_and_attach",
            [
                'waba_id'       => $wabaId,
                'waba_currency' => $currency,
            ]
        );
    }
}
