<?php

namespace Sejator\WabaSdk\Support;

use Illuminate\Support\Arr;
use Sejator\WabaSdk\DTO\NormalizedMessage;
use Sejator\WabaSdk\Enums\MessageType;

class IncomingMessageNormalizer
{
    public function __construct(
        protected MessageTypeResolver $typeResolver,
        protected ComponentNormalizer $componentNormalizer,
    ) {}

    public function normalize(array $message): NormalizedMessage
    {
        $type = $this->typeResolver->resolve($message);

        return new NormalizedMessage(
            type: $type,
            body: $this->extractBody(
                $message,
                $type
            ),
            mediaId: $this->extractMediaId(
                $message,
                $type
            ),
            component: $this->componentNormalizer
                ->normalizeMessage($message),
            payload: $message,
        );
    }

    protected function extractBody(
        array $message,
        string $type
    ): ?string {

        return match ($type) {

            MessageType::TEXT =>
            Arr::get($message, 'text.body'),

            MessageType::IMAGE =>
            Arr::get($message, 'image.caption'),

            MessageType::VIDEO =>
            Arr::get($message, 'video.caption'),

            MessageType::DOCUMENT =>
            Arr::get($message, 'document.caption'),

            MessageType::INTERACTIVE =>
            Arr::get($message, 'interactive.body.text'),

            MessageType::BUTTON =>
            Arr::get($message, 'button.text'),

            MessageType::REACTION =>
            Arr::get($message, 'reaction.emoji'),

            // Fallback Preview Text
            MessageType::AUDIO =>
            '[Audio]',

            MessageType::STICKER =>
            '[Sticker]',

            MessageType::LOCATION =>
            '[Location]',

            MessageType::CONTACTS =>
            '[Contact]',

            MessageType::TEMPLATE =>
            '[Template]',

            default => null,
        };
    }

    protected function extractMediaId(
        array $message,
        string $type
    ): ?string {

        return match ($type) {

            MessageType::IMAGE =>
            Arr::get($message, 'image.id'),

            MessageType::VIDEO =>
            Arr::get($message, 'video.id'),

            MessageType::AUDIO =>
            Arr::get($message, 'audio.id'),

            MessageType::DOCUMENT =>
            Arr::get($message, 'document.id'),

            MessageType::STICKER =>
            Arr::get($message, 'sticker.id'),

            default => null,
        };
    }
}
