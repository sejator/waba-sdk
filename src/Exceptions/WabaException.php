<?php

namespace Sejator\WabaSdk\Exceptions;

use RuntimeException;

class WabaException extends RuntimeException
{
    protected int $status;

    protected array $errors;

    public function __construct(
        string $message,
        int $status = 500,
        array $errors = [],
    ) {
        parent::__construct(
            $message,
            $status
        );

        $this->status = $status;

        $this->errors = $errors;
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP Status
    |--------------------------------------------------------------------------
    */

    public function getStatus(): int
    {
        return $this->status;
    }

    /*
    |--------------------------------------------------------------------------
    | Raw Errors
    |--------------------------------------------------------------------------
    */

    public function getErrors(): array
    {
        return $this->errors;
    }

    /*
    |--------------------------------------------------------------------------
    | Meta Error Code
    |--------------------------------------------------------------------------
    */

    public function getMetaCode(): ?int
    {
        return data_get(
            $this->errors,
            'error.code'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Meta Error Subcode
    |--------------------------------------------------------------------------
    */

    public function getMetaSubcode(): ?int
    {
        return data_get(
            $this->errors,
            'error.error_subcode'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Meta Error Type
    |--------------------------------------------------------------------------
    */

    public function getMetaType(): ?string
    {
        return data_get(
            $this->errors,
            'error.type'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | User Message
    |--------------------------------------------------------------------------
    */

    public function getUserMessage(): ?string
    {
        return data_get(
            $this->errors,
            'error.error_user_msg'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FB Trace ID
    |--------------------------------------------------------------------------
    */

    public function getTraceId(): ?string
    {
        return data_get(
            $this->errors,
            'error.fbtrace_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Is OAuth Error
    |--------------------------------------------------------------------------
    */

    public function isAuthError(): bool
    {
        return in_array(

            $this->getMetaCode(),

            [

                190,

                102,

                10,

            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Is Rate Limit
    |--------------------------------------------------------------------------
    */

    public function isRateLimit(): bool
    {
        return in_array(

            $this->getMetaCode(),

            [

                4,

                17,

                32,

                613,

                80007,

            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Is Retryable
    |--------------------------------------------------------------------------
    */

    public function isRetryable(): bool
    {
        return $this->isRateLimit()
            || $this->status >= 500;
    }

    /*
    |--------------------------------------------------------------------------
    | To Array
    |--------------------------------------------------------------------------
    */

    public function toArray(): array
    {
        return [

            'message' =>
            $this->getMessage(),

            'status' =>
            $this->getStatus(),

            'meta_code' =>
            $this->getMetaCode(),

            'meta_subcode' =>
            $this->getMetaSubcode(),

            'meta_type' =>
            $this->getMetaType(),

            'trace_id' =>
            $this->getTraceId(),

            'errors' =>
            $this->getErrors(),

        ];
    }
}
