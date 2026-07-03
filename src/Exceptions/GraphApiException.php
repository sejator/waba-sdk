<?php

namespace Sejator\WabaSdk\Exceptions;

class GraphApiException extends WabaException
{
    protected ?string $type = null;
    protected ?int $graphCode = null;
    protected ?int $subcode = null;
    protected ?string $userTitle = null;
    protected ?string $userMessage = null;
    protected ?string $fbtraceId = null;

    public static function fromResponse(array $response, int $status = 502): static
    {

        $error = data_get(
            $response,
            'error',
            [],
        );

        $code = data_get(
            $error,
            'code',
        );

        $message = data_get(
            $error,
            'message',
            'Graph API request failed.',
        );

        $exception = match ($code) {

            /*
            |--------------------------------------------------------------------------
            | Recipient not allowed
            |--------------------------------------------------------------------------
            */

            131030 => new RecipientNotAllowedException(
                message: $message,
                status: $status,
                errors: $response,
            ),

            /*
            |--------------------------------------------------------------------------
            | Default
            |--------------------------------------------------------------------------
            */

            default => new static(
                message: $message,
                status: $status,
                errors: $response,
            ),
        };

        $exception->type = data_get(
            $error,
            'type',
        );

        $exception->graphCode = $code;

        $exception->subcode = data_get(
            $error,
            'error_subcode',
        );

        $exception->userTitle = data_get(
            $error,
            'error_user_title',
        );

        $exception->userMessage = data_get(
            $error,
            'error_user_msg',
        );

        $exception->fbtraceId = data_get(
            $error,
            'fbtrace_id',
        );

        return $exception;
    }

    /**
     * Graph API error type.
     */
    public function type(): ?string
    {
        return $this->type;
    }

    /**
     * Graph API error code.
     */
    public function graphCode(): ?int
    {
        return $this->graphCode;
    }

    /**
     * Graph API subcode.
     */
    public function subcode(): ?int
    {
        return $this->subcode;
    }

    /**
     * User friendly title.
     */
    public function userTitle(): ?string
    {
        return $this->userTitle;
    }

    /**
     * User friendly message.
     */
    public function userMessage(): ?string
    {
        return $this->userMessage;
    }

    /**
     * Facebook trace id.
     */
    public function fbtraceId(): ?string
    {
        return $this->fbtraceId;
    }

    /**
     * Retryable Graph API error.
     */
    public function isRetryable(): bool
    {
        return in_array(
            $this->graphCode,
            [
                1,      // Unknown Error
                2,      // API Service
                4,      // Rate Limit
                17,     // User Rate Limit
                32,     // Read Timeout
                341,    // Application Limit
                613,    // Calls To This Api
                80007,  // WhatsApp Rate Limit
            ],
            true,
        );
    }

    /**
     * Authentication error.
     */
    public function isAuthenticationError(): bool
    {
        return in_array(
            $this->graphCode,
            [
                190,
                102,
                104,
            ],
            true,
        );
    }

    /**
     * Permission error.
     */
    public function isPermissionError(): bool
    {
        return in_array(
            $this->graphCode,
            [
                10,
                200,
                294,
            ],
            true,
        );
    }

    /**
     * Validation error.
     */
    public function isValidationError(): bool
    {
        return in_array(
            $this->graphCode,
            [
                100,
                131008,
                131009,
                131021,
            ],
            true,
        );
    }

    /**
     * Convert exception to array.
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'status' => $this->status(),
            'type' => $this->type,
            'code' => $this->graphCode,
            'subcode' => $this->subcode,
            'user_title' => $this->userTitle,
            'user_message' => $this->userMessage,
            'fbtrace_id' => $this->fbtraceId,
            'errors' => $this->errors(),
        ];
    }
}
