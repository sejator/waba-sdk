<?php

namespace Sejator\WabaSdk\DTO;

use Carbon\CarbonInterface;

class EmbeddedSignupSession
{
    public function __construct(

        /*
        |--------------------------------------------------------------------------
        | OAuth State
        |--------------------------------------------------------------------------
        */

        public readonly string $state,

        /*
        |--------------------------------------------------------------------------
        | Session Status
        |--------------------------------------------------------------------------
        */

        public readonly string $status = 'pending',

        /*
        |--------------------------------------------------------------------------
        | OAuth Code
        |--------------------------------------------------------------------------
        */

        public readonly ?string $code = null,

        /*
        |--------------------------------------------------------------------------
        | Business
        |--------------------------------------------------------------------------
        */

        public readonly ?string $businessId = null,

        public readonly ?string $wabaId = null,

        /*
        |--------------------------------------------------------------------------
        | Phone Number
        |--------------------------------------------------------------------------
        */

        public readonly ?string $phoneNumberId = null,

        /*
        |--------------------------------------------------------------------------
        | Token
        |--------------------------------------------------------------------------
        */

        public readonly ?string $accessToken = null,

        /*
        |--------------------------------------------------------------------------
        | Meta Payload
        |--------------------------------------------------------------------------
        */

        public readonly array $payload = [],

        /*
        |--------------------------------------------------------------------------
        | Timestamps
        |--------------------------------------------------------------------------
        */

        public readonly ?CarbonInterface $createdAt = null,

        public readonly ?CarbonInterface $completedAt = null,

    ) {}

    public static function make(string $state): self
    {
        return new self(

            state: $state,

            createdAt: now(),

        );
    }

    public function completed(array $payload = []): self
    {
        return new self(

            state: $this->state,

            status: 'completed',

            code: $this->code,

            businessId: data_get(
                $payload,
                'business_id'
            ),

            wabaId: data_get(
                $payload,
                'waba_id'
            ),

            phoneNumberId: data_get(
                $payload,
                'phone_number_id'
            ),

            accessToken: data_get(
                $payload,
                'access_token'
            ),

            payload: $payload,

            createdAt: $this->createdAt,

            completedAt: now(),

        );
    }

    public function failed(array $payload = []): self
    {
        return new self(

            state: $this->state,

            status: 'failed',

            code: $this->code,

            payload: $payload,

            createdAt: $this->createdAt,

            completedAt: now(),

        );
    }

    public function toArray(): array
    {
        return [
            'state' => $this->state,
            'status' => $this->status,
            'code' => $this->code,
            'business_id' => $this->businessId,
            'waba_id' => $this->wabaId,
            'phone_number_id' => $this->phoneNumberId,
            'access_token' => $this->accessToken,
            'payload' => $this->payload,
            'created_at' => $this->createdAt?->toDateTimeString(),
            'completed_at' => $this->completedAt?->toDateTimeString(),
        ];
    }
}
