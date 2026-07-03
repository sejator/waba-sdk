<?php

namespace Sejator\WabaSdk\Support\Template;

use Illuminate\Support\Collection;
use InvalidArgumentException;

trait VariableNormalizer
{
    /**
     * Ambil seluruh nomor variable.
     *
     * "{{1}} {{2}}"
     * =>
     * collect([1,2])
     */
    protected function variables(string $text): Collection
    {
        preg_match_all(
            '/\{\{(\d+)\}\}/',
            $text,
            $matches,
        );

        return collect($matches[1])
            ->map(
                fn(string $value) => (int) $value,
            )
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Apakah text memiliki variable.
     */
    protected function hasVariables(string $text): bool
    {

        return $this->variables($text)
            ->isNotEmpty();
    }

    /**
     * Validasi urutan variable.
     *
     * {{1}}{{2}}{{3}}
     *
     * valid
     *
     * {{1}}{{3}}
     *
     * invalid
     */
    protected function validateVariables(string $text): void
    {

        $variables = $this->variables(
            $text,
        );

        if ($variables->isEmpty()) {
            return;
        }

        $expected = collect(
            range(
                1,
                $variables->max(),
            ),
        );

        if (
            !$variables->values()->equals(
                $expected,
            )
        ) {
            throw new InvalidArgumentException(
                'Template variables must be sequential.',
            );
        }
    }

    /**
     * Menghasilkan example default.
     *
     * {{1}}{{2}}
     *
     * =>
     *
     * [
     *     "example 1",
     *     "example 2",
     * ]
     */
    protected function variableExamples(string $text): array
    {

        $this->validateVariables(
            $text,
        );

        return $this->variables(
            $text,
        )
            ->map(
                fn(int $index) => "example {$index}",
            )
            ->values()
            ->all();
    }
}
