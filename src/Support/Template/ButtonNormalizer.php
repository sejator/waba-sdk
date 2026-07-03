<?php

namespace Sejator\WabaSdk\Support\Template;

class ButtonNormalizer
{
    use VariableNormalizer;

    /**
     * Normalize BUTTONS component.
     *
     * @param array<string,mixed> $component
     * @return array<string,mixed>
     */
    public function normalize(string $category, array $component): array
    {

        $component['buttons'] = collect(
            $component['buttons'] ?? [],
        )
            ->map(
                fn(array $button) => $this->normalizeButton(
                    $category,
                    $button,
                ),
            )
            ->filter()
            ->values()
            ->all();

        return $component;
    }

    /**
     * Normalize single button.
     *
     * @param array<string,mixed> $button
     * @return array<string,mixed>|null
     */
    protected function normalizeButton(string $category, array $button): ?array
    {

        return match ($button['type'] ?? null) {

            'URL' => $this->url(
                $category,
                $button,
            ),

            'PHONE_NUMBER' => $this->phone(
                $category,
                $button,
            ),

            'COPY_CODE' => $this->copyCode(
                $category,
                $button,
            ),

            'VOICE_CALL' => $this->voiceCall(
                $category,
                $button,
            ),

            'QUICK_REPLY' => $this->quickReply(
                $category,
                $button,
            ),

            default => null,
        };
    }

    /**
     * URL Button.
     *
     * @param array<string,mixed> $button
     * @return array<string,mixed>
     */
    protected function url(string $category, array $button): array
    {

        unset($category);

        $payload = [
            'type' => 'URL',
            'text' => $button['text'] ?? '',
            'url'  => $button['url'] ?? '',
        ];

        /*
        |--------------------------------------------------------------------------
        | Dynamic URL Example
        |--------------------------------------------------------------------------
        */

        if (!empty($button['example'])) {

            $payload['example'] = collect(
                (array) $button['example'],
            )
                ->filter()
                ->values()
                ->all();

            return $payload;
        }

        $examples = $this->variableExamples(
            $button['url'] ?? '',
        );

        if ($examples !== []) {
            $payload['example'] = $examples;
        }

        return $payload;
    }

    /**
     * Phone Button.
     *
     * @param array<string,mixed> $button
     * @return array<string,mixed>
     */
    protected function phone(string $category, array $button): array
    {

        unset($category);

        return [
            'type' => 'PHONE_NUMBER',
            'text' => $button['text'] ?? '',
            'phone_number' => $button['phone_number'] ?? '',
        ];
    }

    /**
     * Quick Reply Button.
     *
     * @param array<string,mixed> $button
     * @return array<string,mixed>
     */
    protected function quickReply(string $category, array $button): array
    {

        unset($category);

        return [
            'type' => 'QUICK_REPLY',
            'text' => $button['text'] ?? '',
        ];
    }

    /**
     * Copy Code Button.
     *
     * @param array<string,mixed> $button
     * @return array<string,mixed>
     */
    protected function copyCode(string $category, array $button): array
    {

        unset($category);

        return [
            'type' => 'COPY_CODE',
            'text' => $button['text'] ?? '',
            'example' => $button['example'] ?? '',
        ];
    }

    /**
     * Voice Call Button.
     *
     * @param array<string,mixed> $button
     * @return array<string,mixed>
     */
    protected function voiceCall(string $category, array $button): array
    {

        unset($category);

        return [
            'type' => 'VOICE_CALL',
            'text' => $button['text'] ?? '',
        ];
    }
}
