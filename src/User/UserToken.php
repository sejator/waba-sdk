<?php

namespace Sejator\WabaSdk\User;

class UserToken
{
    public function __construct(
        public readonly string $userId,
        public readonly string $accessToken
    ) {}
}
