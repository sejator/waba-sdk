<?php

namespace Sejator\WabaSdk\DTO;

use Carbon\CarbonInterface;

class EmbeddedSignupSession
{
    public function __construct(
        public readonly string $state,
        public readonly string $status = 'pending',
        public readonly ?string $code = null,
        public readonly ?string $accessToken = null,
        public readonly ?string $wabaId = null,
        public readonly ?string $phoneNumberId = null,
        public readonly array $context = [],
        public readonly array $metadata = [],
        public readonly ?CarbonInterface $createdAt = null,
        public readonly ?CarbonInterface $completedAt = null,
    ) {
        //
    }

    public static function make(array $attributes = []): self
    {
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
            accessToken: data_get(
                $attributes,
                'access_token'
            ),
            wabaId: data_get(
                $attributes,
                'waba_id'
            ),
            phoneNumberId: data_get(
                $attributes,
                'phone_number_id'
            ),
            context: data_get(
                $attributes,
                'context',
                []
            ),
            metadata: data_get(
                $attributes,
                'metadata',
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

    public function completed(array $attributes = []): self
    {
        return new self(
            state: $this->state,
            status: 'completed',
            code: data_get(
                $attributes,
                'code',
                $this->code
            ),
            accessToken: data_get(
                $attributes,
                'access_token',
                $this->accessToken
            ),
            wabaId: data_get(
                $attributes,
                'waba_id',
                $this->wabaId
            ),
            phoneNumberId: data_get(
                $attributes,
                'phone_number_id',
                $this->phoneNumberId
            ),
            context: $this->context,
            metadata: data_get(
                $attributes,
                'metadata',
                []
            ),
            createdAt: $this->createdAt,
            completedAt: now(),
        );
    }

    public function failed(array $attributes = []): self
    {
        return new self(
            state: $this->state,
            status: 'failed',
            code: $this->code,
            accessToken: $this->accessToken,
            wabaId: $this->wabaId,
            phoneNumberId: $this->phoneNumberId,
            context: $this->context,
            metadata: data_get(
                $attributes,
                'metadata',
                []
            ),
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
            'access_token' => $this->accessToken,
            'waba_id' => $this->wabaId,
            'phone_number_id' => $this->phoneNumberId,
            'context' => $this->context,
            'metadata' => $this->metadata,
            'created_at' => $this->createdAt?->toDateTimeString(),
            'completed_at' => $this->completedAt?->toDateTimeString(),
        ];
    }
}
