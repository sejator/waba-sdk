<?php

namespace Sejator\WabaSdk\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Sejator\WabaSdk\Exceptions\WabaException;

class MediaService
{
    public function __construct(
        protected Client $client
    ) {}

    public function upload(string $phoneNumberId, UploadedFile|string $file, ?string $type = null,): array
    {
        $endpoint = "/{$phoneNumberId}/media";

        if ($file instanceof UploadedFile) {
            if (!$file->isValid()) {
                throw new WabaException(
                    'Invalid uploaded file.'
                );
            }

            $stream = fopen($file->getRealPath(), 'r');

            try {

                return $this->client->multipart(
                    $endpoint,
                    [
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
                    ]
                );
            } finally {
                fclose($stream);
            }
        }

        if (is_string($file) && file_exists($file)) {

            $stream = fopen($file, 'r');

            try {
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

                return $this->client->multipart(
                    $endpoint,
                    $multipart
                );
            } finally {
                fclose($stream);
            }
        }

        throw new WabaException(
            'Invalid file input.'
        );
    }

    public function uploadMediaTemplate(string $appId, string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new WabaException(
                "File tidak ditemukan: {$filePath}"
            );
        }

        $mime = File::mimeType($filePath);

        if (!$mime) {

            throw new WabaException(
                'Gagal mendeteksi mime type.'
            );
        }

        $allowed = ['image/jpeg', 'image/png', 'video/mp4', 'application/pdf'];

        if (!in_array($mime, $allowed)) {
            throw new WabaException(
                "Mime type tidak didukung: {$mime}"
            );
        }


        $size = filesize($filePath);

        if (!$size) {

            throw new WabaException(
                'Gagal membaca ukuran file.'
            );
        }

        $session = $this->client
            ->postWithQuery(
                "/{$appId}/uploads",
                [
                    'file_name' => basename(
                        $filePath
                    ),
                    'file_length' => $size,
                    'file_type' => $mime,
                ]
            );

        $uploadId = data_get(
            $session,
            'id'
        );

        if (!$uploadId) {

            throw new WabaException(
                'Gagal membuat upload session.'
            );
        }

        $binary = file_get_contents(
            $filePath
        );

        if ($binary === false) {

            throw new WabaException(
                'Gagal membaca file binary.'
            );
        }

        $response = $this->client
            ->uploadBinary(
                $uploadId,
                $binary
            );

        $handle = data_get(
            $response,
            'h'
        );

        if (!$handle) {

            throw new WabaException(
                'Upload gagal: media handle tidak ditemukan.'
            );
        }

        return $handle;
    }

    public function uploadAndGetId(string $phoneNumberId, UploadedFile|string $file, ?string $type = null): ?string
    {
        return data_get(
            $this->upload(
                $phoneNumberId,
                $file,
                $type
            ),
            'id'
        );
    }

    public function get(string $mediaId): array
    {
        return $this->client->get(
            "/{$mediaId}"
        );
    }

    public function download(string $mediaUrl): string
    {
        return $this->client
            ->getRaw($mediaUrl);
    }

    public function delete(string $mediaId): bool
    {
        $this->client->delete(
            "/{$mediaId}"
        );

        return true;
    }
}
