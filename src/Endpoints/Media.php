<?php

namespace Sejator\WabaSdk\Endpoints;

use Sejator\WabaSdk\Http\WabaClient;
use Sejator\WabaSdk\Exceptions\WabaException;

class Media
{
    public function __construct(
        protected WabaClient $client
    ) {}

    /**
     * Upload media (image, video, audio, document)
     */
    public function upload(string $filePath, string $mimeType): array
    {
        if (!is_readable($filePath)) {
            throw new WabaException("File not readable: {$filePath}");
        }

        return $this->client->multipart('media', [
            'messaging_product' => 'whatsapp',
            'type' => $mimeType,
            'file' => fopen($filePath, 'r'),
        ]);
    }

    /**
     * Retrieve media info / download URL
     */
    public function retrieve(string $mediaId): array
    {
        return $this->client->get($mediaId);
    }
}
