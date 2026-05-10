<?php

namespace Sejator\WabaSdk\Services;

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
        return $this->client->post(
            $this->endpoint($phoneNumberId),
            $payload
        );
    }

    public function text(string $phoneNumberId, string $to, string $text)
    {
        return $this->send($phoneNumberId, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'body' => $text,
            ],
        ]);
    }

    public function media(
        string $phoneNumberId,
        string $to,
        string $type,
        string $url,
        ?string $caption = null
    ) {
        return $this->send($phoneNumberId, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => $type,
            $type => array_filter([
                'link' => $url,
                'caption' => $caption,
            ]),
        ]);
    }

    public function template(string $phoneNumberId, array $payload)
    {
        return $this->send($phoneNumberId, $payload);
    }

    public function interactive(string $phoneNumberId, array $payload)
    {
        return $this->send($phoneNumberId, $payload);
    }
}
