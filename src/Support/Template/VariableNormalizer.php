<?php

namespace Sejator\WabaSdk\Support\Template;

use Illuminate\Support\Collection;
use InvalidArgumentException;

trait VariableNormalizer
{
    /**
     * Extract variable numbers.
     */
    protected function variables(
        string $text,
    ): Collection {

        preg_match_all(
            '/\{\{(\d+)\}\}/',
            $text,
            $matches,
        );

        return collect($matches[1])
            ->map(
                fn (string $value) => (int) $value,
            )
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Determine whether text contains variables.
     */
    protected function hasVariables(
        string $text,
    ): bool {

        return $this->variables($text)
            ->isNotEmpty();
    }

    /**
     * Ensure variables are sequential.
     */
    protected function validateVariables(
        Collection $variables,
    ): void {

        if ($variables->isEmpty()) {
            return;
        }

        $expected = collect(
            range(
                1,
                $variables->max(),
            ),
        );

        if ($variables->values()->all() !== $expected->values()->all()) {
            throw new InvalidArgumentException(
                'Template variables must be sequential.',
            );
        }
    }

    /**
     * Generate default variable examples.
     */
    protected function variableExamples(
        string $text,
    ): array {

        $variables = $this->variables(
            $text,
        );

        $this->validateVariables(
            $variables,
        );

        return $variables
            ->map(
                fn (int $index) => "example {$index}",
            )
            ->all();
    }
}