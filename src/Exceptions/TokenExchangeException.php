<?php

namespace Sejator\WabaSdk\Exceptions;

class TokenExchangeException extends GraphApiException
{
    public function isExpiredCode(): bool
    {
        return in_array(
            $this->getMetaSubcode(),
            [
                1349152,
            ]
        );
    }

    public function isInvalidRedirect(): bool
    {
        return str_contains(
            strtolower(
                $this->getMessage()
            ),
            'redirect'
        );
    }
}
