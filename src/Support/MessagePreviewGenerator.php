<?php

namespace Sejator\WabaSdk\Support;

class MessagePreviewGenerator
{
    public function generate(array $message): array
    {
        return [
            'text' => $this->resolveText($message),

            'media' => filled(
                $message['media_url']
                    ?? $message['media_id']
                    ?? null
            ),
        ];
    }

    protected function resolveText(
        array $message
    ): string {

        // Cek dulu sebelum body generik ($message['body'] selalu terisi
        // teks placeholder "[Contact]" untuk tipe ini) - biar preview
        // tampilkan nama kontak asli, bukan label generik.
        if (($message['type'] ?? null) === 'contacts') {
            return $this->resolveContactsText($message);
        }

        if (!empty($message['body'])) {
            return $message['body'];
        }

        return match ($message['type'] ?? null) {
            'image' => '📷 Image',
            'video' => '🎥 Video',
            'audio' => '🎵 Audio',
            'document' => '📄 Document',
            'sticker' => '🏷️ Sticker',
            'location' => '📍 Location',
            'contacts' => '👤 Contact',
            'reaction' => '😀 Reaction',
            'template' => '📢 Template',
            'interactive' => '📋 Interactive',
            default => 'Message',
        };
    }

    protected function resolveContactsText(array $message): string
    {
        $contacts = $message['component']['contacts'] ?? [];

        if (empty($contacts)) {
            return '👤 Contact';
        }

        $name = $contacts[0]['name']['formatted_name']
            ?? $contacts[0]['name']['first_name']
            ?? 'Contact';

        $extra = count($contacts) - 1;

        return $extra > 0
            ? "👤 {$name} (+{$extra} lainnya)"
            : "👤 {$name}";
    }
}
