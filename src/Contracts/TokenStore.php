<?php

namespace Sejator\WabaSdk\Contracts;

interface TokenStore
{

    public function put(string $key, array $payload): void;

    public function get(string $key): ?array;

    public function accessToken(string $key): ?string;

    public function refreshToken(string $key): ?string;

    public function expired(string $key): bool;

    public function forget(string $key): void;

    public function exists(string $key): bool;
}
