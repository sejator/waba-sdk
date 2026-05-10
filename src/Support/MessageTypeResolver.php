<?php

namespace Sejator\WabaSdk\Support;

use Illuminate\Support\Arr;
use Sejator\WabaSdk\Enums\MessageType;

class MessageTypeResolver
{
    public function resolve(array $message): string
    {
        $type = Arr::get($message, 'type');

        return match ($type) {
            'text' => MessageType::TEXT,
            'image' => MessageType::IMAGE,
            'video' => MessageType::VIDEO,
            'audio' => MessageType::AUDIO,
            'document' => MessageType::DOCUMENT,
            'sticker' => MessageType::STICKER,
            'template' => MessageType::TEMPLATE,
            'interactive' => MessageType::INTERACTIVE,
            'button' => MessageType::BUTTON,
            'location' => MessageType::LOCATION,
            'contacts' => MessageType::CONTACTS,
            'reaction' => MessageType::REACTION,
            default => MessageType::UNKNOWN,
        };
    }
}
