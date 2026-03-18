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
     * Get media metadata (url, mime, size)
     */
    public function retrieve(string $mediaId): array
    {
        if (empty($mediaId)) {
            throw new WabaException('mediaId is required');
        }

        return $this->client->get($mediaId);
    }

    /**
     * Delete media (optional but useful)
     */
    public function delete(string $mediaId): array
    {
        if (empty($mediaId)) {
            throw new WabaException('mediaId is required');
        }

        return $this->client->delete($mediaId);
    }
}
