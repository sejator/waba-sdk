<?php

namespace Sejator\WabaSdk\DTO;

class NormalizedMessage
{
    public function __construct(
        public string $type,
        public ?string $body = null,
        public ?string $mediaId = null,
        public ?string $mediaUrl = null,
        public ?string $mimeType = null,
        public ?string $filename = null,
        public ?array $component = [],
        public ?array $payload = [],
    ) {}
}
