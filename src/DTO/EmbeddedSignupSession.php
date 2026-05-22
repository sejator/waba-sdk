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
        | Context
        |--------------------------------------------------------------------------
        |
        | Generic application context.
        | Example:
        | - tenant_id
        | - user_id
        | - redirect_url
        | - provider
        |
        */

        public readonly array $context = [],

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

    public static function make(
        array $attributes = []
    ): self {

        return new self(
            state: (string) data_get(
                $attributes,
                'state'
            ),

            status: (string) data_get(
                $attributes,
                'status',
                'pending'
            ),

            code: data_get(
                $attributes,
                'code'
            ),

            businessId: data_get(
                $attributes,
                'business_id'
            ),

            wabaId: data_get(
                $attributes,
                'waba_id'
            ),

            phoneNumberId: data_get(
                $attributes,
                'phone_number_id'
            ),

            accessToken: data_get(
                $attributes,
                'access_token'
            ),

            context: data_get(
                $attributes,
                'context',
                []
            ),

            payload: data_get(
                $attributes,
                'payload',
                []
            ),

            createdAt: data_get(
                $attributes,
                'created_at',
                now()
            ),

            completedAt: data_get(
                $attributes,
                'completed_at'
            ),

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
            context: $this->context,
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
            businessId: $this->businessId,
            wabaId: $this->wabaId,
            phoneNumberId: $this->phoneNumberId,
            accessToken: $this->accessToken,
            context: $this->context,
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
            'context' => $this->context,
            'payload' => $this->payload,
            'created_at' => $this->createdAt?->toDateTimeString(),
            'completed_at' => $this->completedAt?->toDateTimeString(),
        ];
    }
}
