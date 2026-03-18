<?php

namespace Sejator\WabaSdk\Webhook;

use Illuminate\Support\Arr;
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
            throw new RuntimeException('Invalid webhook payload');
        }

        $this->payload = $data;
        $this->entry   = Arr::get($data, 'entry.0', []);
        $this->change  = Arr::get($this->entry, 'changes.0', []);
        $this->value   = Arr::get($this->change, 'value', []);
    }

    /* ================= BASIC ================= */

    public function raw(): array
    {
        return $this->payload;
    }

    public function field(): ?string
    {
        return $this->change['field'] ?? null;
    }

    public function wabaId(): ?string
    {
        return $this->entry['id'] ?? null;
    }

    public function phoneNumberId(): ?string
    {
        return Arr::get($this->value, 'metadata.phone_number_id');
    }

    public function displayPhoneNumber(): ?string
    {
        return Arr::get($this->value, 'metadata.display_phone_number');
    }

    /* ================= EVENT TYPE ================= */

    public function type(): string
    {
        return match (true) {
            $this->isIncomingMessage() => 'message',
            $this->isStatus()          => 'status',
            $this->isTemplateEvent()   => 'template',
            $this->isAccountEvent()    => 'account',
            $this->isPhoneEvent()      => 'phone',
            default                   => 'unknown',
        };
    }

    public function eventId(): string
    {
        return $this->messageId()
            ?? $this->statusId()
            ?? sha1(json_encode($this->payload));
    }

    /* ================= MESSAGE ================= */

    public function isIncomingMessage(): bool
    {
        return !empty($this->value['messages']);
    }

    public function messages(): array
    {
        return $this->value['messages'] ?? [];
    }

    public function message(): ?array
    {
        return $this->messages()[0] ?? null;
    }

    public function messageId(): ?string
    {
        return Arr::get($this->message(), 'id');
    }

    public function messageFrom(): ?string
    {
        return Arr::get($this->message(), 'from');
    }

    public function messageType(): ?string
    {
        return Arr::get($this->message(), 'type');
    }

    public function messageText(): ?string
    {
        return Arr::get($this->message(), 'text.body');
    }

    public function messageMediaId(): ?string
    {
        return Arr::get($this->message(), 'image.id')
            ?? Arr::get($this->message(), 'video.id')
            ?? Arr::get($this->message(), 'audio.id')
            ?? Arr::get($this->message(), 'document.id');
    }

    public function messageTimestamp(): ?string
    {
        return Arr::get($this->message(), 'timestamp');
    }

    /* ================= STATUS ================= */

    public function isStatus(): bool
    {
        return !empty($this->value['statuses']);
    }

    public function statuses(): array
    {
        return $this->value['statuses'] ?? [];
    }

    public function status(): ?array
    {
        return $this->statuses()[0] ?? null;
    }

    public function statusId(): ?string
    {
        return Arr::get($this->status(), 'id');
    }

    public function statusValue(): ?string
    {
        return Arr::get($this->status(), 'status'); // sent, delivered, read
    }

    public function statusRecipient(): ?string
    {
        return Arr::get($this->status(), 'recipient_id');
    }

    /* ================= CONTACT ================= */

    public function contact(): ?array
    {
        return Arr::get($this->value, 'contacts.0');
    }

    public function contactName(): ?string
    {
        return Arr::get($this->contact(), 'profile.name');
    }

    public function waId(): ?string
    {
        return Arr::get($this->contact(), 'wa_id');
    }

    /* ================= SYSTEM EVENTS ================= */

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

    /* ================= SUMMARY ================= */

    public function summary(): array
    {
        return [
            'type'            => $this->type(),
            'event_id'        => $this->eventId(),
            'waba_id'         => $this->wabaId(),
            'phone_number_id' => $this->phoneNumberId(),
            'from'            => $this->messageFrom(),
            'message_type'    => $this->messageType(),
            'status'          => $this->statusValue(),
        ];
    }
}
