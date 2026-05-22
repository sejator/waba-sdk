<?php

namespace Sejator\WabaSdk\Support\Embedded;

use Illuminate\Support\Facades\Cache;
use Sejator\WabaSdk\DTO\EmbeddedSignupSession;

class EmbeddedStateManager
{
    protected string $prefix = 'waba_embedded_state:';

    protected int $ttl = 300;

    public function put(
        EmbeddedSignupSession $session
    ): void {

        Cache::put(

            $this->key($session->state),

            $session->toArray(),

            now()->addSeconds($this->ttl)

        );
    }

    public function get(
        string $state
    ): ?EmbeddedSignupSession {

        $payload = Cache::get(
            $this->key($state)
        );

        if (!$payload) {
            return null;
        }

        return new EmbeddedSignupSession(

            state: data_get($payload, 'state'),

            status: data_get(
                $payload,
                'status',
                'pending'
            ),

            code: data_get($payload, 'code'),

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

            payload: data_get(
                $payload,
                'payload',
                []
            ),

            createdAt: data_get(
                $payload,
                'created_at'
            )
                ? now()->parse(
                    data_get(
                        $payload,
                        'created_at'
                    )
                )
                : null,

            completedAt: data_get(
                $payload,
                'completed_at'
            )
                ? now()->parse(
                    data_get(
                        $payload,
                        'completed_at'
                    )
                )
                : null,

        );
    }

    public function forget(string $state): void
    {
        Cache::forget(
            $this->key($state)
        );
    }

    public function exists(string $state): bool
    {
        return Cache::has(
            $this->key($state)
        );
    }

    protected function key(string $state): string
    {
        return $this->prefix . $state;
    }
}
