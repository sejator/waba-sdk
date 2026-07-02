<?php

class TemplateComponentNormalizer
{
    public function __construct(
        protected HeaderNormalizer $header,
        protected BodyNormalizer $body,
        protected ButtonNormalizer $buttons,
    ) {}

    public function normalize(array $components): array
    {
        return collect($components)
            ->filter(fn($component) => $this->shouldKeep($component))
            ->map(fn($component) => $this->normalizeComponent($component))
            ->values()
            ->all();
    }

    protected function normalizeComponent(array $component): array
    {
        return match ($component['type'] ?? null) {

            'HEADER' => $this->header->normalize($component),

            'BODY' => $this->body->normalize($component),

            'BUTTONS' => $this->buttons->normalize($component),

            default => $component,
        };
    }

    protected function shouldKeep(array $component): bool
    {
        if (($component['type'] ?? null) !== 'HEADER') {
            return true;
        }

        if (($component['format'] ?? null) !== 'TEXT') {
            return true;
        }

        return filled($component['text'] ?? null);
    }
}
