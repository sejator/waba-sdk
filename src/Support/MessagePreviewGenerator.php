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
}
