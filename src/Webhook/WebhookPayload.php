<?php

namespace Sejator\WabaSdk\Webhook;

use RuntimeException;

class WebhookPayload
{
    protected array $payload;
    protected array $entry;
    protected array $change;
    protected array $value;

    public function __construct(string $rawPayload)
    {
        $data = json_decode($rawPayload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Payload webhook tidak valid');
        }

        $this->payload = $data;
        $this->entry   = $data['entry'][0] ?? [];
        $this->change  = $this->entry['changes'][0] ?? [];
        $this->value   = $this->change['value'] ?? [];
    }

    /** ID WhatsApp Business Account */
    public function wabaId(): ?string
    {
        return $this->entry['id'] ?? null;
    }

    public function field(): ?string
    {
        return $this->change['field'] ?? null;
    }

    public function eventId(): ?string
    {
        if ($this->isIncomingMessage()) {
            return $this->value['messages'][0]['id'] ?? null;
        }

        if ($this->isStatus()) {
            return $this->value['statuses'][0]['id'] ?? null;
        }

        return sha1($this->field() . ':' . $this->wabaId() . ':' . ($this->accountEvent() ?? ''));
    }

    public function phoneNumberId(): ?string
    {
        return $this->value['metadata']['phone_number_id'] ?? null;
    }

    public function displayPhoneNumber(): ?string
    {
        return $this->value['metadata']['display_phone_number'] ?? null;
    }

    /* -----------------------------------------------------------------
     | Raw payload accessors
     |----------------------------------------------------------------- */

    public function raw(): array
    {
        return $this->payload;
    }

    public function entry(): array
    {
        return $this->entry;
    }

    public function change(): array
    {
        return $this->change;
    }

    public function value(): array
    {
        return $this->value;
    }

    /* -----------------------------------------------------------------
     | Event
     |----------------------------------------------------------------- */

    public function isIncomingMessage(): bool
    {
        return isset($this->value['messages']) && is_array($this->value['messages']);
    }

    public function isStatus(): bool
    {
        return isset($this->value['statuses']) && is_array($this->value['statuses']);
    }

    public function isTemplateEvent(): bool
    {
        return str_starts_with((string) $this->field(), 'message_template');
    }

    public function isAccountEvent(): bool
    {
        return in_array($this->field(), [
            'account_update',
            'account_settings_update',
            'business_status_update',
        ], true);
    }

    public function isPhoneEvent(): bool
    {
        return in_array($this->field(), [
            'phone_number_quality_update',
            'phone_number_name_update',
        ], true);
    }

    public function isSystemEvent(): bool
    {
        return $this->isAccountEvent()
            || $this->isPhoneEvent()
            || $this->isTemplateEvent();
    }

    /* -----------------------------------------------------------------
     | Message helpers
     |----------------------------------------------------------------- */

    public function messages(): array
    {
        return $this->value['messages'] ?? [];
    }

    public function message(): ?array
    {
        return $this->isIncomingMessage()
            ? ($this->messages()[0] ?? null)
            : null;
    }

    public function messageFrom(): ?string
    {
        return $this->message()['from'] ?? null;
    }

    public function messageType(): ?string
    {
        return $this->message()['type'] ?? null;
    }

    public function messageText(): ?string
    {
        return $this->message()['text']['body'] ?? null;
    }

    public function hasTextMessage(): bool
    {
        return $this->messageType() === 'text'
            && !empty($this->messageText());
    }

    public function messageMediaId(): ?string
    {
        if (!$this->isIncomingMessage()) {
            return null;
        }

        return $this->message()['image']['id']
            ?? $this->message()['video']['id']
            ?? $this->message()['audio']['id']
            ?? null;
    }

    public function messageTimestamp(): ?string
    {
        return $this->message()['timestamp'] ?? null;
    }

    /* -----------------------------------------------------------------
     | Status helpers
     |----------------------------------------------------------------- */

    public function statuses(): array
    {
        return $this->value['statuses'] ?? [];
    }

    /* -----------------------------------------------------------------
     | Template helpers
     |----------------------------------------------------------------- */

    public function templateName(): ?string
    {
        return $this->value['message_template_name'] ?? null;
    }

    public function templateLanguage(): ?string
    {
        return $this->value['message_template_language'] ?? null;
    }

    public function templateStatus(): ?string
    {
        return $this->value['event'] ?? null;
    }

    public function templateQuality(): ?string
    {
        return $this->value['quality_score'] ?? null;
    }

    /* -----------------------------------------------------------------
     | Account & phone helpers
     |----------------------------------------------------------------- */

    public function accountEvent(): ?string
    {
        return $this->value['event'] ?? null;
    }

    public function accountStatus(): ?string
    {
        return $this->value['account_status'] ?? null;
    }

    public function wabaInfo(): array
    {
        return $this->value['waba_info'] ?? [];
    }

    public function ownerBusinessId(): ?string
    {
        return $this->value['waba_info']['owner_business_id'] ?? null;
    }

    public function phoneQuality(): ?string
    {
        return $this->value['quality_rating'] ?? null;
    }

    public function verifiedName(): ?string
    {
        return $this->value['verified_name'] ?? null;
    }

    public function summary(): array
    {
        return [
            'field'           => $this->field(),
            'event_id'        => $this->eventId(),
            'waba_id'         => $this->wabaId(),
            'phone_number_id' => $this->phoneNumberId(),
            'from'            => $this->messageFrom(),
            'message_type'    => $this->messageType(),
        ];
    }
}
