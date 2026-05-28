<?php

namespace Sejator\WabaSdk\Exceptions;

class GraphApiException extends WabaException
{
    public static function fromResponse(array $response, int $status = 502): static
    {
        $code = data_get(
            $response,
            'error.code'
        );

        return match ($code) {
            131030 => new RecipientNotAllowedException(
                message: data_get(
                    $response,
                    'error.message',
                    'Recipient phone number not allowed.'
                ),
                status: $status,
                errors: $response,
            ),

            default => new static(
                message: data_get(
                    $response,
                    'error.message',
                    'Graph API request failed.'
                ),
                status: $status,
                errors: $response,
            ),
        };
    }
}
