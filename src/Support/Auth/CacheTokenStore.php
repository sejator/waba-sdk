<?php

namespace Sejator\WabaSdk\Support\Auth;

use Illuminate\Support\Facades\Cache;
use Sejator\WabaSdk\Contracts\TokenStore;

class CacheTokenStore implements TokenStore
{
    protected string $prefix = 'waba_token:';
    protected int $ttl;

    public function __construct()
    {
        $this->ttl = config('waba.auth.token_ttl', 86400);
    }

    public function put(string $key, array $payload): void
    {
        $expiresAt = data_get(
            $payload,
            'expires_at'
        );

        if ($expiresAt) {
            Cache::put(
                $this->key($key),
                $payload,
                $expiresAt
            );

            return;
        }

        Cache::put(
            $this->key($key),
            $payload,
            now()->addSeconds(
                $this->ttl
            )
        );
    }

    public function get(string $key): ?array
    {
        return Cache::get(
            $this->key($key)
        );
    }

    public function accessToken(string $key): ?string
    {
        return data_get(
            $this->get($key),
            'access_token'
        );
    }

    public function refreshToken(string $key): ?string
    {
        return data_get(
            $this->get($key),
            'refresh_token'
        );
    }

    public function expired(string $key): bool
    {
        $payload = $this->get($key);

        if (!$payload) {
            return true;
        }

        $expiresAt = data_get(
            $payload,
            'expires_at'
        );

        if (!$expiresAt) {
            return false;
        }

        return now()->gte(
            $expiresAt
        );
    }

    public function forget(string $key): void
    {

        Cache::forget(
            $this->key($key)
        );
    }

    public function exists(string $key,): bool
    {
        return Cache::has(
            $this->key($key)
        );
    }

    protected function key(string $key): string
    {
        return $this->prefix . $key;
    }
}
