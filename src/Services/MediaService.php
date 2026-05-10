<?php

namespace Sejator\WabaSdk\Services;

use Illuminate\Http\UploadedFile;
use Sejator\WabaSdk\Exceptions\WabaException;

class MediaService
{
    public function __construct(
        protected Client $client
    ) {}

    /**
     * Upload media
     */
    public function upload(string $phoneNumberId, $file, ?string $type = null): array
    {
        $endpoint = "/{$phoneNumberId}/media";

        if ($file instanceof UploadedFile) {

            if (!$file->isValid()) {
                throw new WabaException('Invalid uploaded file');
            }

            $stream = fopen($file->getRealPath(), 'r');

            $multipart = [
                [
                    'name' => 'file',
                    'contents' => $stream,
                    'filename' => $file->getClientOriginalName(),
                ],
                [
                    'name' => 'messaging_product',
                    'contents' => 'whatsapp',
                ],
                [
                    'name' => 'type',
                    'contents' => $type ?? $file->getMimeType(),
                ],
            ];

            $response = $this->client->multipart($endpoint, $multipart);

            fclose($stream);

            return $response;
        }

        if (is_string($file) && file_exists($file)) {

            $stream = fopen($file, 'r');

            $multipart = [
                [
                    'name' => 'file',
                    'contents' => $stream,
                    'filename' => basename($file),
                ],
                [
                    'name' => 'messaging_product',
                    'contents' => 'whatsapp',
                ],
            ];

            if ($type) {
                $multipart[] = [
                    'name' => 'type',
                    'contents' => $type,
                ];
            }

            $response = $this->client->multipart($endpoint, $multipart);

            fclose($stream);

            return $response;
        }

        throw new WabaException('Invalid file input');
    }

    public function uploadMediaTemplate(string $appId, string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new WabaException("File tidak ditemukan: {$filePath}");
        }

        $fileSize = filesize($filePath);
        $fileName = basename($filePath);
        $mime = mime_content_type($filePath);

        $allowed = [
            'image/jpeg',
            'image/png',
            'video/mp4',
            'application/pdf',
        ];

        if (!in_array($mime, $allowed)) {
            throw new WabaException("Mime type tidak didukung: {$mime}");
        }

        $session = $this->client->postWithQuery("/{$appId}/uploads", [
            'file_name'   => $fileName,
            'file_length' => $fileSize,
            'file_type'   => $mime,
        ]);

        if (!isset($session['id'])) {
            throw new WabaException('Gagal membuat upload session');
        }

        $uploadId = $session['id'];

        $binary = file_get_contents($filePath);

        $res = $this->client->uploadBinary($uploadId, $binary);

        if (!isset($res['h'])) {
            throw new WabaException('Upload gagal: handle tidak ditemukan');
        }

        return $res['h'];
    }

    /**
     * Shortcut ambil media_id
     */
    public function uploadAndGetId(string $phoneNumberId, $file, ?string $type = null): ?string
    {
        $res = $this->upload($phoneNumberId, $file, $type);

        return $res['id'] ?? null;
    }

    /**
     * Get media metadata
     */
    public function get(string $mediaId): array
    {
        return $this->client->get("/{$mediaId}");
    }

    /**
     * Download media binary
     */
    public function download(string $mediaUrl)
    {
        return $this->client->getRaw($mediaUrl);
    }

    /**
     * Delete media
     */
    public function delete(string $mediaId): bool
    {
        $this->client->delete("/{$mediaId}");

        return true;
    }
}
