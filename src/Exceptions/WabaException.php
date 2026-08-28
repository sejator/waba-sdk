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

    /**
     * Detail spesifik dari Meta (error.error_data.details), lebih actionable
     * dari getMessage() yang cuma judul generik.
     */
    public function getErrorDetails(): ?string
    {
        return data_get(
            $this->errors,
            'error.error_data.details'
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
            [4, 17, 32, 613, 80007, 130429, 131048, 131049, 131056,]
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

    /**
     * Seluruh kode resmi dari daftar Meta:
     * https://developers.facebook.com/documentation/business-messaging/whatsapp/support/error-codes
     */
    public function getErrorKey(): string
    {
        return match ($this->getMetaCode()) {

            // Auth / permission
            0, 190 => 'token_expired',
            102 => 'invalid_token',
            10, 3 => 'permission_denied',
            104, 200 => 'missing_access_token',

            // Rate limit / throttling
            4, 17, 80007 => 'rate_limit',
            130429 => 'throughput_limit',
            131048 => 'spam_rate_limit',
            131049 => 'quality_rate_limit',
            131056 => 'recipient_rate_limit',
            133016 => 'registration_rate_limit',

            // Delivery
            130403 => 'business_blocked_recipient',
            131026 => 'message_undeliverable',
            131047 => 're_engagement_required',
            131050 => 'marketing_opted_out',
            130472 => 'experiment_not_sent',
            131051 => 'unsupported_message_type',
            131037 => 'display_name_not_approved',

            // Validation / parameter
            100 => 'invalid_parameter',
            131008 => 'missing_parameter',
            131009 => 'invalid_parameter_value',
            131021 => 'sender_recipient_same',

            // Media
            131052 => 'media_download_failed',
            131053 => 'media_upload_failed',

            // Billing / account
            131042 => 'payment_method_error',
            33 => 'phone_number_deleted',
            368 => 'account_restricted',
            134011 => 'payments_terms_pending',

            // Template
            132000 => 'template_param_count_mismatch',
            132001 => 'template_not_found_or_unapproved',
            132005 => 'template_text_too_long',
            132007 => 'template_policy_violation',
            132012 => 'template_param_format_mismatch',
            132015 => 'template_paused_low_quality',
            132016 => 'template_disabled',
            132018 => 'template_param_validation_error',
            131055, 134100 => 'marketing_template_only',
            131063 => 'marketing_template_disabled',
            2388012 => 'phone_number_already_migrated',
            2388019 => 'template_limit_exceeded',
            2388039 => 'template_status_change_invalid',
            2388040 => 'template_field_too_long',
            2388047 => 'template_header_format_invalid',
            2388072 => 'template_body_format_invalid',
            2388073 => 'template_footer_format_invalid',
            2388293 => 'template_param_word_ratio_exceeded',
            2388299 => 'template_leading_trailing_param',
            134101 => 'template_still_syncing',
            134102 => 'template_unavailable',
            200005 => 'template_insights_unavailable',
            200006 => 'template_insights_cannot_disable',
            200007 => 'template_insights_not_enabled',

            // Flow
            132068 => 'flow_blocked',
            132069 => 'flow_throttled',

            // Registrasi / verifikasi nomor
            133000 => 'deregistration_previous_attempt_failed',
            133005 => 'two_step_pin_incorrect',
            133006 => 'phone_needs_verification',
            133008 => 'two_step_pin_attempts_exceeded',
            133009 => 'two_step_pin_too_fast',
            133010 => 'phone_not_registered',
            133015 => 'phone_recently_deleted',
            2388091, 2388093 => 'phone_ineligible_for_verification',
            2388103 => 'phone_migration_ineligible',
            2593107 => 'sync_rate_limit_exceeded',
            2593108 => 'sync_outside_onboarding_window',
            1752041 => 'duplicate_onboarding_request',
            2494100 => 'account_maintenance_mode',
            131057 => 'account_maintenance_mode',

            // Server / unknown
            1, 133004 => 'server_error',
            2 => 'server_overloaded',
            131016 => 'service_unavailable',
            131000, 135000 => 'unknown_error',

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
