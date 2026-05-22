<?php

namespace Sejator\WabaSdk\DTO;

class PhoneNumber
{
    public function __construct(

        /*
        |--------------------------------------------------------------------------
        | Meta Phone Number ID
        |--------------------------------------------------------------------------
        */

        public readonly string $id,

        /*
        |--------------------------------------------------------------------------
        | Display Number
        |--------------------------------------------------------------------------
        */

        public readonly ?string $displayPhoneNumber = null,

        /*
        |--------------------------------------------------------------------------
        | Verified Name
        |--------------------------------------------------------------------------
        */

        public readonly ?string $verifiedName = null,

        /*
        |--------------------------------------------------------------------------
        | Quality Rating
        |--------------------------------------------------------------------------
        */

        public readonly ?string $qualityRating = null,

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        public readonly ?string $status = null,

        /*
        |--------------------------------------------------------------------------
        | Platform Type
        |--------------------------------------------------------------------------
        */

        public readonly ?string $platformType = null,

        /*
        |--------------------------------------------------------------------------
        | Throughput
        |--------------------------------------------------------------------------
        */

        public readonly ?string $throughput = null,

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

            displayPhoneNumber: data_get(
                $data,
                'display_phone_number'
            ),

            verifiedName: data_get(
                $data,
                'verified_name'
            ),

            qualityRating: data_get(
                $data,
                'quality_rating'
            ),

            status: data_get(
                $data,
                'status'
            ),

            platformType: data_get(
                $data,
                'platform_type'
            ),

            throughput: data_get(
                $data,
                'throughput.level'
            ),

            payload: $data,

        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'display_phone_number' => $this->displayPhoneNumber,
            'verified_name' => $this->verifiedName,
            'quality_rating' => $this->qualityRating,
            'status' => $this->status,
            'platform_type' => $this->platformType,
            'throughput' => $this->throughput,
            'payload' => $this->payload,
        ];
    }
}
