<?php

namespace Sejator\WabaSdk\DTO;

class WabaAccount
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name = null,
        public readonly ?string $businessId = null,
        public readonly ?string $businessName = null,
        public readonly ?string $currency = null,
        public readonly ?string $timezoneId = null,
        public readonly ?string $messageTemplateNamespace = null,
        public readonly ?string $accountReviewStatus = null,
        public readonly ?string $ownershipType = null,
        public readonly array $metadata = [],
    ) {
        //
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) data_get(
                $data,
                'id'
            ),
            name: data_get(
                $data,
                'name'
            ),
            businessId: data_get(
                $data,
                'business_id'
            ),
            businessName: data_get(
                $data,
                'business_name'
            ),
            currency: data_get(
                $data,
                'currency'
            ),
            timezoneId: data_get(
                $data,
                'timezone_id'
            ),
            messageTemplateNamespace: data_get(
                $data,
                'message_template_namespace'
            ),
            accountReviewStatus: data_get(
                $data,
                'account_review_status'
            ),
            ownershipType: data_get(
                $data,
                'ownership_type'
            ),
            metadata: data_get(
                $data,
                'metadata',
                []
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'business_id' => $this->businessId,
            'business_name' => $this->businessName,
            'currency' => $this->currency,
            'timezone_id' => $this->timezoneId,
            'message_template_namespace' => $this->messageTemplateNamespace,
            'account_review_status' => $this->accountReviewStatus,
            'ownership_type' => $this->ownershipType,
            'metadata' => $this->metadata,
        ];
    }
}
