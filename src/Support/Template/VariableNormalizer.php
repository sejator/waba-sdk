<?php

namespace Sejator\WabaSdk\Support\Template;

use Illuminate\Support\Collection;
use InvalidArgumentException;

trait VariableNormalizer
{
    protected function variables(string $text): Collection
    {
        preg_match_all('/\{\{(\d+)\}\}/', $text, $matches);

        $vars = collect($matches[1])
            ->map(fn($i) => (int) $i)
            ->unique()
            ->sort()
            ->values();

        if ($vars->isEmpty()) {
            return $vars;
        }

        $expected = range(
            1,
            $vars->max()
        );

        if ($vars->all() !== $expected) {
            throw new InvalidArgumentException(
                'Template variables must be sequential.'
            );
        }

        return $vars;
    }
}
