<?php

namespace Sejator\WabaSdk\Support\Template;

class BodyNormalizer
{
    use VariableNormalizer;

    /**
     * Normalize BODY component.
     *
     * @param array<string,mixed> $component
     * @return array<string,mixed>
     */
    public function normalize(string $category, array $component): array
    {

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
     * Normalize BODY for Utility & Marketing.
     *
     * @param array<string,mixed> $component
     * @return array<string,mixed>
     */
    protected function normalizeStandard(array $component): array
    {

        if (!empty($component['example']['body_text'])) {
            return $component;
        }

        $examples = $this->variableExamples(
            $component['text'] ?? '',
        );

        if ($examples === []) {
            return $component;
        }

        $component['example'] = [
            'body_text' => [
                $examples,
            ],
        ];

        return $component;
    }

    /**
     * Normalize BODY for Authentication Template.
     *
     * Authentication memiliki aturan khusus yang akan
     * ditambahkan pada refactor berikutnya.
     *
     * @param array<string,mixed> $component
     * @return array<string,mixed>
     */
    protected function normalizeAuthentication(array $component): array
    {

        return $this->normalizeStandard(
            $component,
        );
    }
}
