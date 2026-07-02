<?php

class ButtonNormalizer
{
    public function normalize(array $component): array
    {
        $component['buttons'] = collect(
            $component['buttons'] ?? []
        )
            ->map(fn($button) => $this->normalizeButton($button))
            ->filter()
            ->values()
            ->all();

        return $component;
    }

    protected function normalizeButton(array $button): array
    {
        return match ($button['type'] ?? null) {

            'URL' => $this->url($button),

            'PHONE_NUMBER' => $this->phone($button),

            'COPY_CODE' => $this->copyCode($button),

            'VOICE_CALL' => $this->voiceCall($button),

            'QUICK_REPLY' => $this->quickReply($button),

            default => $button,
        };
    }

    protected function url(array $button): array
    {
        $payload = [
            'type' => 'URL',
            'text' => $button['text'],
            'url' => $button['url'],
        ];

        if (!empty($button['example'])) {
            $payload['example'] = array_values(
                (array) $button['example']
            );
        }

        return $payload;
    }

    protected function phone(array $button): array
    {
        return [
            'type' => 'PHONE_NUMBER',
            'text' => $button['text'],
            'phone_number' => $button['phone_number'],
        ];
    }

    protected function quickReply(array $button): array
    {
        return [
            'type' => 'QUICK_REPLY',
            'text' => $button['text'],
        ];
    }

    protected function copyCode(array $button): array
    {
        return [
            'type' => 'COPY_CODE',
            'text' => $button['text'],
            'example' => $button['example'] ?? '',
        ];
    }

    protected function voiceCall(array $button): array
    {
        return [
            'type' => 'VOICE_CALL',
            'text' => $button['text'],
        ];
    }
}
