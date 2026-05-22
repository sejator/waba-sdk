<?php

namespace Sejator\WabaSdk\Contracts;

interface StateStore
{
    public function put(string $state, array $payload, int $ttl = 300): void;

    public function get(string $state): ?array;

    public function forget(string $state): void;
}
