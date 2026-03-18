<?php

namespace Sejator\WabaSdk\Billing;

use InvalidArgumentException;
use Sejator\WabaSdk\Http\WabaClient;

class CreditManager
{
    public function __construct(
        protected WabaClient $client
    ) {}

    public function listCreditLines(string $businessId): array
    {
        $this->validateId($businessId, 'businessId');

        return $this->client->get(
            "{$businessId}/extendedcredits",
            [
                'fields' => 'id,legal_entity_name,status,currency'
            ]
        );
    }

    public function attachCreditLine(
        string $creditLineId,
        string $wabaId,
        string $currency
    ): array {
        $this->validateId($creditLineId, 'creditLineId');
        $this->validateId($wabaId, 'wabaId');

        if (empty($currency)) {
            throw new InvalidArgumentException('currency is required');
        }

        return $this->client->post(
            "{$creditLineId}/whatsapp_credit_sharing_and_attach",
            [
                'waba_id'       => $wabaId,
                'waba_currency' => strtoupper($currency),
            ]
        );
    }

    public function detachCreditLine(
        string $creditLineId,
        string $wabaId
    ): array {
        $this->validateId($creditLineId, 'creditLineId');
        $this->validateId($wabaId, 'wabaId');

        return $this->client->post(
            "{$creditLineId}/whatsapp_credit_sharing_and_detach",
            [
                'waba_id' => $wabaId,
            ]
        );
    }

    protected function validateId(string $value, string $field): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException("{$field} is required");
        }
    }
}
