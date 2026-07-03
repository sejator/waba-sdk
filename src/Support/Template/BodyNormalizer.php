<?php

namespace Sejator\WabaSdk\Support\Template;

class BodyNormalizer
{
    use VariableNormalizer;

    /**
     * Normalize BODY component.
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
     * Normalize BODY for Marketing & Utility template.
     *
     * @param  array<string,mixed>  $component
     * @return array<string,mixed>
     */
    protected function normalizeStandard(
        array $component,
    ): array {

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
     * Normalize BODY for Authentication template.
     *
     * Authentication template memiliki struktur BODY
     * yang berbeda dengan Marketing/Utility.
     * Untuk sementara tetap menggunakan normalisasi
     * standar sampai seluruh rule Authentication
     * selesai diimplementasikan.
     *
     * @param  array<string,mixed>  $component
     * @return array<string,mixed>
     */
    protected function normalizeAuthentication(
        array $component,
    ): array {

        $component = $this->normalizeStandard(
            $component,
        );

        /*
        |--------------------------------------------------------------------------
        | Authentication Rules
        |--------------------------------------------------------------------------
        |
        | Placeholder untuk implementasi:
        | - add_security_recommendation
        | - code_expiration_minutes
        | - zero-tap / copy-code
        |
        */

        return $component;
    }
}
