<?php

namespace Sejator\WabaSdk\Services;

class QrCodeService
{
    public function __construct(
        protected Client $client,
    ) {}

    public function all(string $phoneNumberId): array
    {
        return $this->client->get(
            "{$phoneNumberId}/message_qrdls",
        );
    }

    public function find(string $phoneNumberId, string $code): array
    {
        return $this->client->get(
            "{$phoneNumberId}/message_qrdls/{$code}",
        );
    }

    public function create(string $phoneNumberId, string $prefilledMessage, string $imageFormat = 'SVG'): array
    {
        return $this->client->post(
            "{$phoneNumberId}/message_qrdls",
            [
                'prefilled_message' => $prefilledMessage,
                'generate_qr_image' => $imageFormat,
            ],
        );
    }

    public function update(string $phoneNumberId, string $code, string $prefilledMessage): array
    {
        return $this->client->post(
            "{$phoneNumberId}/message_qrdls",
            [
                'code' => $code,
                'prefilled_message' => $prefilledMessage,
            ],
        );
    }

    public function delete(string $phoneNumberId, string $code): array
    {
        return $this->client->delete(
            "{$phoneNumberId}/message_qrdls/{$code}",
        );
    }
}
