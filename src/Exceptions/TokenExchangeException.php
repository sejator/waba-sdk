<?php

namespace Sejator\WabaSdk\Exceptions;

class TokenExchangeException extends GraphApiException
{
    /*
    |--------------------------------------------------------------------------
    | Is Expired Authorization Code
    |--------------------------------------------------------------------------
    */

    public function isExpiredCode(): bool
    {
        return in_array(

            $this->getMetaSubcode(),

            [

                1349152,

            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Invalid Redirect URI
    |--------------------------------------------------------------------------
    */

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
