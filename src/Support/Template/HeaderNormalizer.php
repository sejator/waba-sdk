<?php

namespace Sejator\WabaSdk\Support\Template;

class HeaderNormalizer
{
    use VariableNormalizer;

    /**
     * Normalize Header component.
     *
     * @param  array<string,mixed>  $component
     * @return array<string,mixed>
     */
    public function normalize(string $category, array $component): array
    {

        return match ($component['format'] ?? null) {

            'TEXT' => $this->normalizeText(
                $category,
                $component,
            ),

            'IMAGE',
            'VIDEO',
            'DOCUMENT' => $this->normalizeMedia(
                $category,
                $component,
            ),

            default => $component,
        };
    }

    /**
     * Normalize Text Header.
     *
     * @param  array<string,mixed>  $component
     * @return array<string,mixed>
     */
    protected function normalizeText(string $category, array $component): array
    {

        // Reserved for future Authentication Header rules.
        unset($category);

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
    protected function normalizeMedia(string $category, array $component): array
    {

        // Reserved for future Authentication Header rules.
        unset($category);

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
