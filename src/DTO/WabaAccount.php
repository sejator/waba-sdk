<?php

namespace Sejator\WabaSdk\DTO;

class WabaAccount
{
    public function __construct(

        /*
        |--------------------------------------------------------------------------
        | WABA ID
        |--------------------------------------------------------------------------
        */

        public readonly string $id,

        /*
        |--------------------------------------------------------------------------
        | WABA Name
        |--------------------------------------------------------------------------
        */

        public readonly ?string $name = null,

        /*
        |--------------------------------------------------------------------------
        | Currency
        |--------------------------------------------------------------------------
        */

        public readonly ?string $currency = null,

        /*
        |--------------------------------------------------------------------------
        | Timezone
        |--------------------------------------------------------------------------
        */

        public readonly ?string $timezoneId = null,

        /*
        |--------------------------------------------------------------------------
        | Message Template Namespace
        |--------------------------------------------------------------------------
        */

        public readonly ?string $messageTemplateNamespace = null,

        /*
        |--------------------------------------------------------------------------
        | Ownership Type
        |--------------------------------------------------------------------------
        */

        public readonly ?string $ownershipType = null,

        /*
        |--------------------------------------------------------------------------
        | Raw Payload
        |--------------------------------------------------------------------------
        */

        public readonly array $payload = [],

    ) {}

    public static function fromArray(array $data): self
    {
        return new self(

            id: (string) data_get($data, 'id'),

            name: data_get($data, 'name'),

            currency: data_get($data, 'currency'),

            timezoneId: data_get(
                $data,
                'timezone_id'
            ),

            messageTemplateNamespace: data_get(
                $data,
                'message_template_namespace'
            ),

            ownershipType: data_get(
                $data,
                'ownership_type'
            ),

            payload: $data,

        );
    }

    public function toArray(): array
    {
        return [

            'id' => $this->id,

            'name' => $this->name,

            'currency' => $this->currency,

            'timezone_id' => $this->timezoneId,

            'message_template_namespace' =>
            $this->messageTemplateNamespace,

            'ownership_type' =>
            $this->ownershipType,

            'payload' => $this->payload,

        ];
    }
}
