<?php

namespace Sejator\WabaSdk\Contracts;

interface TokenStore
{
    public function put(string $key, string $token): void;

    public function get(string $key): ?string;

    public function forget(string $key): void;
}
