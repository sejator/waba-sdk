<?php

namespace Sejator\WabaSdk\Support\Template;

class BodyNormalizer
{
    use VariableNormalizer;

    public function normalize(array $component): array
    {
        if (!empty($component['example'])) {
            return $component;
        }

        $vars = $this->variables(
            $component['text'] ?? ''
        );

        if ($vars->isEmpty()) {
            return $component;
        }

        $component['example'] = [
            'body_text' => [
                $vars
                    ->map(fn($i) => "example {$i}")
                    ->all(),
            ],
        ];

        return $component;
    }
}
