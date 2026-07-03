<?php

namespace Sejator\WabaSdk\Support\Template;

class ButtonNormalizer
{
    use VariableNormalizer;

    /**
     * Normalize BUTTONS component.
     *
     * @param  array<string,mixed>  $component
     * @return array<string,mixed>
     */
    public function normalize(
        string $category,
        array $component,
    ): array {

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
     * @param  array<string,mixed>  $button
     * @return array<string,mixed>|null
     */
    protected function normalizeButton(
        string $category,
        array $button,
    ): ?array {

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
     * Normalize URL button.
     *
     * @param  array<string,mixed>  $button
     * @return array<string,mixed>
     */
    protected function url(
        string $category,
        array $button,
    ): array {

        $payload = [
            'type' => 'URL',
            'text' => $this->sanitizeButtonText(
                $button['text'] ?? '',
            ),
            'url' => $button['url'] ?? '',
        ];

        if ($category === 'AUTHENTICATION') {
            return $payload;
        }

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
     * Normalize Phone button.
     *
     * @param  array<string,mixed>  $button
     * @return array<string,mixed>
     */
    protected function phone(
        string $category,
        array $button,
    ): array {

        return [
            'type' => 'PHONE_NUMBER',
            'text' => $this->sanitizeButtonText(
                $button['text'] ?? '',
            ),
            'phone_number' => $button['phone_number'] ?? '',
        ];
    }

    /**
     * Normalize Quick Reply button.
     *
     * @param  array<string,mixed>  $button
     * @return array<string,mixed>
     */
    protected function quickReply(
        string $category,
        array $button,
    ): array {

        return [
            'type' => 'QUICK_REPLY',
            'text' => $this->sanitizeButtonText(
                $button['text'] ?? '',
            ),
        ];
    }

    /**
     * Normalize Copy Code button.
     *
     * Authentication menggunakan OTP Button.
     *
     * @param  array<string,mixed>  $button
     * @return array<string,mixed>
     */
    protected function copyCode(
        string $category,
        array $button,
    ): array {

        $text = $this->sanitizeButtonText(
            $button['text'] ?? 'Copy Code',
        );

        if ($text === '') {
            $text = 'Copy Code';
        }

        if ($category === 'AUTHENTICATION') {

            return [
                'type' => 'OTP',
                'otp_type' => 'COPY_CODE',
                'text' => $text,
            ];
        }

        return [
            'type' => 'COPY_CODE',
            'text' => $text,
            'example' => $button['example'] ?? '',
        ];
    }

    /**
     * Normalize Voice Call button.
     *
     * @param  array<string,mixed>  $button
     * @return array<string,mixed>
     */
    protected function voiceCall(
        string $category,
        array $button,
    ): array {

        return [
            'type' => 'VOICE_CALL',
            'text' => $this->sanitizeButtonText(
                $button['text'] ?? '',
            ),
        ];
    }

    /**
     * Remove unsupported characters from button text.
     *
     * Meta tidak mengizinkan:
     * - Variable
     * - Markdown
     * - Emoji
     * - New line
     * - Karakter khusus
     */
    protected function sanitizeButtonText(
        string $text,
    ): string {

        // Remove variables
        $text = preg_replace(
            '/\{\{\d+\}\}/',
            '',
            $text,
        );

        // Remove markdown characters
        $text = str_replace(
            [
                '*',
                '_',
                '~',
                '`',
            ],
            '',
            $text,
        );

        // Remove new line
        $text = preg_replace(
            '/[\r\n]+/',
            ' ',
            $text,
        );

        // Keep only letters, numbers and spaces
        $text = preg_replace(
            '/[^\p{L}\p{N}\s]/u',
            '',
            $text,
        );

        // Normalize spaces
        $text = preg_replace(
            '/\s+/',
            ' ',
            $text,
        );

        return trim($text);
    }
}
