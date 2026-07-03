<?php

namespace Sejator\WabaSdk\Support\Template;

class TemplateComponentNormalizer
{
    public function __construct(
        protected HeaderNormalizer $header,
        protected BodyNormalizer $body,
        protected ButtonNormalizer $buttons,
    ) {}

    /**
     * Normalize template components.
     *
     * @param  string  $category
     * @param  array<int,array<string,mixed>>  $components
     * @return array<int,array<string,mixed>>
     */
    public function normalize(string $category, array $components): array
    {

        return collect($components)
            ->filter(
                fn(array $component) => $this->shouldKeep(
                    $component,
                ),
            )
            ->map(
                fn(array $component) => $this->normalizeComponent(
                    $category,
                    $component,
                ),
            )
            ->values()
            ->all();
    }

    /**
     * Normalize a single component.
     *
     * @param array<string,mixed> $component
     */
    protected function normalizeComponent(string $category, array $component): array
    {

        return match ($component['type'] ?? null) {

            'HEADER' => $this->header->normalize(
                $category,
                $component,
            ),

            'BODY' => $this->body->normalize(
                $category,
                $component,
            ),

            'BUTTONS' => $this->buttons->normalize(
                $category,
                $component,
            ),

            default => $component,
        };
    }

    /**
     * Remove empty components.
     *
     * @param array<string,mixed> $component
     */
    protected function shouldKeep(array $component): bool
    {

        if (($component['type'] ?? null) !== 'HEADER') {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Media Header
        |--------------------------------------------------------------------------
        */

        if (($component['format'] ?? null) !== 'TEXT') {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Empty Text Header
        |--------------------------------------------------------------------------
        */

        return filled(
            $component['text'] ?? null,
        );
    }
}
