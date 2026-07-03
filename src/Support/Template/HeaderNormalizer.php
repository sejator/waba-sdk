<?php

namespace Sejator\WabaSdk\Support\Template;

class HeaderNormalizer
{
    use VariableNormalizer;

    /**
     * Normalize HEADER component.
     *
     * @param  array<string,mixed>  $component
     * @return array<string,mixed>
     */
    public function normalize(
        string $category,
        array $component,
    ): array {

        return match ($category) {

            'AUTHENTICATION' => $this->normalizeAuthentication(
                $component,
            ),

            default => $this->normalizeStandard(
                $component,
            ),
        };
    }

    /**
     * Normalize HEADER for Marketing & Utility template.
     *
     * @param  array<string,mixed>  $component
     * @return array<string,mixed>
     */
    protected function normalizeStandard(
        array $component,
    ): array {

        return match ($component['format'] ?? null) {

            'TEXT' => $this->normalizeText(
                $component,
            ),

            'IMAGE',
            'VIDEO',
            'DOCUMENT' => $this->normalizeMedia(
                $component,
            ),

            default => $component,
        };
    }

    /**
     * Normalize HEADER for Authentication template.
     *
     * Authentication memiliki aturan Header yang
     * berbeda dan akan ditambahkan pada refactor
     * berikutnya.
     *
     * @param  array<string,mixed>  $component
     * @return array<string,mixed>
     */
    protected function normalizeAuthentication(
        array $component,
    ): array {

        return $this->normalizeStandard(
            $component,
        );
    }

    /**
     * Normalize Text Header.
     *
     * @param  array<string,mixed>  $component
     * @return array<string,mixed>
     */
    protected function normalizeText(
        array $component,
    ): array {

        if (!empty($component['example']['header_text'])) {
            return $component;
        }

        $examples = $this->variableExamples(
            $component['text'] ?? '',
        );

        if ($examples === []) {
            return $component;
        }

        $component['example'] = [
            'header_text' => $examples,
        ];

        return $component;
    }

    /**
     * Normalize Media Header.
     *
     * @param  array<string,mixed>  $component
     * @return array<string,mixed>
     */
    protected function normalizeMedia(
        array $component,
    ): array {

        $handle = data_get(
            $component,
            'example.header_handle.0',
        );

        if (!$handle) {
            return $component;
        }

        $component['example'] = [
            'header_handle' => [
                $handle,
            ],
        ];

        return $component;
    }
}
