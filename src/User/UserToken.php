<?php

namespace Sejator\WabaSdk\User;

class UserToken
{
    public function __construct(
        public readonly string $userId,
        public readonly string $accessToken
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'access_token' => $this->accessToken,
        ];
    }
}
