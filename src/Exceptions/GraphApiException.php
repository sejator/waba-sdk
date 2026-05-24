<?php

namespace Sejator\WabaSdk\Exceptions;

class GraphApiException extends WabaException
{
    /*
    |--------------------------------------------------------------------------
    | Create From Graph Response
    |--------------------------------------------------------------------------
    */

    public static function fromResponse(
        array $response,
        int $status = 500,
    ): static {

        return new static(

            message: data_get(

                $response,

                'error.message',

                'Graph API request failed.'
            ),

            status: $status,

            errors: $response,
        );
    }
}
