<?php

namespace Sejator\WabaSdk\Support\Embedded;

use Illuminate\Support\Facades\Cache;
use Sejator\WabaSdk\DTO\EmbeddedSignupSession;

class EmbeddedStateManager
{
    protected string $prefix = 'waba_embedded_state:';

    protected int $ttl = 300;

    public function put(EmbeddedSignupSession $session): void
    {

        Cache::put(
            $this->key($session->state),
            $session->toArray(),
            now()->addSeconds(
                $this->ttl
            )
        );
    }

    public function get(string $state): ?EmbeddedSignupSession
    {

        $payload = Cache::get(
            $this->key($state)
        );

        if (!$payload) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Restore DTO
        |--------------------------------------------------------------------------
        */

        return EmbeddedSignupSession::make([
            ...$payload,
            'created_at' =>
            data_get($payload, 'created_at')
                ? now()->parse(
                    data_get(
                        $payload,
                        'created_at'
                    )
                )
                : null,

            'completed_at' =>
            data_get($payload, 'completed_at')
                ? now()->parse(
                    data_get(
                        $payload,
                        'completed_at'
                    )
                )
                : null,

        ]);
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

    public function pull(string $state): ?EmbeddedSignupSession
    {

        $session = $this->get($state);

        $this->forget($state);

        return $session;
    }

    protected function key(string $state): string
    {
        return $this->prefix . $state;
    }
}
