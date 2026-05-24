<?php

namespace Sejator\WabaSdk\Contracts;

use Sejator\WabaSdk\DTO\EmbeddedSignupSession;

interface StateStore
{
    public function put(EmbeddedSignupSession $session): void;

    public function get(string $state): ?EmbeddedSignupSession;

    public function pull(string $state): ?EmbeddedSignupSession;

    public function exists(string $state): bool;

    public function forget(string $state): void;
}
