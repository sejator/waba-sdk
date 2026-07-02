<?php

namespace Sejator\WabaSdk\Support\Template;

use VariableNormalizer;

class HeaderNormalizer
{
    use VariableNormalizer;

    public function normalize(array $component): array
    {
        if (!empty($component['example'])) {
            return $component;
        }

        return match ($component['format'] ?? null) {

            'TEXT' => $this->normalizeText($component),

            'IMAGE',
            'VIDEO',
            'DOCUMENT'
            => $this->normalizeMedia($component),

            default => $component,
        };
    }

    protected function normalizeText(array $component): array
    {
        $vars = $this->variables(
            $component['text'] ?? ''
        );

        if ($vars->isEmpty()) {
            return $component;
        }

        $component['example'] = [
            'header_text' => $vars
                ->map(fn($i) => "example {$i}")
                ->all(),
        ];

        return $component;
    }

    protected function normalizeMedia(array $component): array
    {
        $handle = data_get(
            $component,
            'example.header_handle.0'
        );

        if ($handle) {

            $component['example'] = [
                'header_handle' => [$handle],
            ];
        }

        return $component;
    }
}
