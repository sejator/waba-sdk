<?php

namespace Sejator\WabaSdk\Endpoints;

use Sejator\WabaSdk\Http\WabaClient;
use Sejator\WabaSdk\Utils\Phone;
use Sejator\WabaSdk\Exceptions\WabaException;

class Message
{
    public function __construct(
        protected WabaClient $client,
        protected string $phoneNumberId
    ) {}

    public function text(string $to, string $message): array
    {
        return $this->send('text', $to, [
            'text' => ['body' => $message],
        ]);
    }

    public function image(string $to, string $link, ?string $caption = null): array
    {
        return $this->send('image', $to, [
            'image' => array_filter([
                'link' => $link,
                'caption' => $caption,
            ]),
        ]);
    }

    public function video(string $to, string $link, ?string $caption = null): array
    {
        return $this->send('video', $to, [
            'video' => array_filter([
                'link' => $link,
                'caption' => $caption,
            ]),
        ]);
    }

    public function document(
        string $to,
        string $link,
        ?string $filename = null,
        ?string $caption = null
    ): array {
        return $this->send('document', $to, [
            'document' => array_filter([
                'link' => $link,
                'filename' => $filename,
                'caption' => $caption,
            ]),
        ]);
    }

    public function audio(string $to, string $link): array
    {
        return $this->send('audio', $to, [
            'audio' => ['link' => $link],
        ]);
    }

    public function sticker(string $to, string $link): array
    {
        return $this->send('sticker', $to, [
            'sticker' => ['link' => $link],
        ]);
    }

    public function interactive(string $to, array $interactive): array
    {
        return $this->send('interactive', $to, [
            'interactive' => $interactive,
        ]);
    }

    public function reaction(string $to, string $messageId, string $emoji): array
    {
        return $this->send('reaction', $to, [
            'reaction' => [
                'message_id' => $messageId,
                'emoji' => $emoji,
            ],
        ]);
    }

    public function template(
        string $to,
        string $name,
        string $language,
        array $components = []
    ): array {
        return $this->send('template', $to, [
            'template' => [
                'name' => $name,
                'language' => ['code' => $language],
                'components' => $components,
            ],
        ]);
    }

    protected function send(
        string $type,
        string $to,
        array $payload,
        string $recipientType = 'individual'
    ): array {
        $to = Phone::normalize($to);

        if (!$to) {
            throw new WabaException('Invalid destination phone number');
        }

        return $this->client->post(
            "{$this->phoneNumberId}/messages",
            array_merge([
                'messaging_product' => 'whatsapp',
                'recipient_type' => $recipientType,
                'to' => $to,
                'type' => $type,
            ], $payload)
        );
    }
}
