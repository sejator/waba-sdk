<?php

namespace Sejator\WabaSdk\Services;

use InvalidArgumentException;

class MessageService
{
    public function __construct(
        protected Client $client
    ) {}

    protected function endpoint(string $phoneNumberId): string
    {
        return "/{$phoneNumberId}/messages";
    }

    public function send(string $phoneNumberId, array $payload): array
    {
        $payload['messaging_product'] ??=
            'whatsapp';

        if (empty($payload['to'])) {

            throw new InvalidArgumentException(
                'Recipient phone number is required.'
            );
        }

        if (empty($payload['type'])) {

            throw new InvalidArgumentException(
                'Message type is required.'
            );
        }

        return $this->client->post(
            $this->endpoint(
                $phoneNumberId
            ),
            $payload
        );
    }

    public function text(string $phoneNumberId, string $to, string $text, bool $previewUrl = false): array
    {
        return $this->send(
            $phoneNumberId,
            [
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'preview_url' => $previewUrl,
                    'body' => $text,
                ],
            ]
        );
    }

    public function media(string $phoneNumberId, string $to, string $type, string $url, ?string $caption = null, ?string $filename = null): array
    {

        $allowed = [
            'image',
            'video',
            'audio',
            'document',
            'sticker',
        ];

        if (!in_array($type, $allowed)) {

            throw new InvalidArgumentException(
                "Unsupported media type: {$type}"
            );
        }

        $media = [
            'link' => $url,
        ];

        if ($caption) {
            $media['caption'] = $caption;
        }

        if ($type === 'document' && $filename) {
            $media['filename'] = $filename;
        }

        return $this->send(
            $phoneNumberId,
            [
                'to' => $to,
                'type' => $type,
                $type => $media,
            ]
        );
    }

    public function template(string $phoneNumberId, array $payload): array
    {
        $payload['type'] = 'template';
        return $this->send(
            $phoneNumberId,
            $payload
        );
    }

    public function interactive(string $phoneNumberId, array $payload): array
    {
        $payload['type'] = 'interactive';
        return $this->send(
            $phoneNumberId,
            $payload
        );
    }

    public function markAsRead(string $phoneNumberId, string $messageId): array
    {
        return $this->send(
            $phoneNumberId,
            [
                'status' => 'read',
                'message_id' => $messageId,
                'type' => 'reaction',
                'reaction' => [
                    'message_id' => $messageId,
                    'emoji' => '👍',
                ],
            ]
        );
    }
}
