<?php

namespace Sejator\WabaSdk\Exceptions;

use RuntimeException;

class WabaException extends RuntimeException
{
    protected int $status;
    protected array $errors;

    public function __construct(string $message, int $status = 502, array $errors = [])
    {
        parent::__construct(
            $message,
            $status
        );

        $this->status = $status;
        $this->errors = $errors;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getMetaCode(): ?int
    {
        return data_get(
            $this->errors,
            'error.code'
        );
    }

    public function getMetaSubcode(): ?int
    {
        return data_get(
            $this->errors,
            'error.error_subcode'
        );
    }

    public function getMetaType(): ?string
    {
        return data_get(
            $this->errors,
            'error.type'
        );
    }

    public function getUserMessage(): ?string
    {
        return data_get(
            $this->errors,
            'error.error_user_msg'
        );
    }

    public function getTraceId(): ?string
    {
        return data_get(
            $this->errors,
            'error.fbtrace_id'
        );
    }

    public function isAuthError(): bool
    {
        return in_array(
            $this->getMetaCode(),
            [190, 102, 10]
        );
    }

    public function isRateLimit(): bool
    {
        return in_array(
            $this->getMetaCode(),
            [4, 17, 32, 613, 80007, 130429, 131048,]
        );
    }

    public function isRecipientNotAllowed(): bool
    {
        return $this->getMetaCode() === 131030;
    }

    public function isUndeliverable(): bool
    {
        return $this->getMetaCode() === 131026;
    }

    public function isReEngagementRequired(): bool
    {
        return $this->getMetaCode() === 131047;
    }

    public function isTemporaryBlock(): bool
    {
        return $this->getMetaCode() === 368;
    }

    public function isRetryable(): bool
    {
        if ($this->isRecipientNotAllowed()) {
            return false;
        }

        if ($this->isUndeliverable()) {
            return false;
        }

        if ($this->isReEngagementRequired()) {
            return false;
        }

        return $this->isRateLimit()
            || $this->isTemporaryBlock()
            || $this->status >= 500;
    }

    public function getErrorKey(): string
    {
        return match ($this->getMetaCode()) {
            190 => 'token_expired',
            102 => 'invalid_token',
            10 => 'permission_denied',
            131030 => 'recipient_not_allowed',
            131026 => 'message_undeliverable',
            131047 => 're_engagement_required',
            131048 => 'spam_rate_limit',
            130429 => 'throughput_limit',
            80007 => 'rate_limit',
            368 => 'temporary_block',
            default => 'graph_api_error',
        };
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'user_message' => $this->getUserMessage(),
            'status' => $this->getStatus(),
            'meta_code' => $this->getMetaCode(),
            'meta_subcode' => $this->getMetaSubcode(),
            'meta_type' => $this->getMetaType(),
            'trace_id' => $this->getTraceId(),
            'error_key' => $this->getErrorKey(),
            'retryable' => $this->isRetryable(),
            'errors' => $this->getErrors(),
        ];
    }
}
