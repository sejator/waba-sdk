<?php

namespace Sejator\WabaSdk\DTO;

class EmbeddedSignupResult
{
    public function __construct(

        /*
        |--------------------------------------------------------------------------
        | Access Token
        |--------------------------------------------------------------------------
        */

        public readonly string $accessToken,

        /*
        |--------------------------------------------------------------------------
        | Meta Business
        |--------------------------------------------------------------------------
        */

        public readonly ?string $businessId = null,

        public readonly ?string $businessName = null,

        /*
        |--------------------------------------------------------------------------
        | WhatsApp Business Account
        |--------------------------------------------------------------------------
        */

        public readonly ?string $wabaId = null,

        public readonly ?string $wabaName = null,

        /*
        |--------------------------------------------------------------------------
        | Phone Number
        |--------------------------------------------------------------------------
        */

        public readonly ?string $phoneNumberId = null,

        public readonly ?string $displayPhoneNumber = null,

        /*
        |--------------------------------------------------------------------------
        | Embedded Signup Status
        |--------------------------------------------------------------------------
        */

        public readonly ?string $status = null,

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

            accessToken: (string) data_get(
                $data,
                'access_token',
                ''
            ),

            businessId: data_get(
                $data,
                'business_id'
            ),

            businessName: data_get(
                $data,
                'business_name'
            ),

            wabaId: data_get(
                $data,
                'waba_id'
            ),

            wabaName: data_get(
                $data,
                'waba_name'
            ),

            phoneNumberId: data_get(
                $data,
                'phone_number_id'
            ),

            displayPhoneNumber: data_get(
                $data,
                'display_phone_number'
            ),

            status: data_get(
                $data,
                'status'
            ),

            payload: $data,

        );
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'business_id' => $this->businessId,
            'business_name' => $this->businessName,
            'waba_id' => $this->wabaId,
            'waba_name' => $this->wabaName,
            'phone_number_id' => $this->phoneNumberId,
            'display_phone_number' => $this->displayPhoneNumber,
            'status' => $this->status,
            'payload' => $this->payload,
        ];
    }
}
