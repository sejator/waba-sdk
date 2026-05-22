<?php

namespace Sejator\WabaSdk\Support\Webhooks;

use Sejator\WabaSdk\DTO\NormalizedMessage;
use Sejator\WabaSdk\Support\IncomingMessageNormalizer;

class WebhookParser
{
    public function __construct(
        protected IncomingMessageNormalizer $normalizer,
    ) {}

    public function parse(array $payload): array
    {
        $messages = [];

        foreach (
            data_get($payload, 'entry', [])
            as $entry
        ) {

            foreach (
                data_get($entry, 'changes', [])
                as $change
            ) {

                $value = data_get(
                    $change,
                    'value',
                    []
                );

                /*
                |--------------------------------------------------------------------------
                | Incoming Messages
                |--------------------------------------------------------------------------
                */

                foreach (
                    data_get($value, 'messages', [])
                    as $message
                ) {

                    $messages[] = $this->normalizer
                        ->normalize([
                            'message' => $message,
                            'metadata' => data_get(
                                $value,
                                'metadata',
                                []
                            ),
                            'contacts' => data_get(
                                $value,
                                'contacts',
                                []
                            ),
                            'statuses' => data_get(
                                $value,
                                'statuses',
                                []
                            ),
                        ]);
                }
            }
        }

        return $messages;
    }

    public function first(
        array $payload
    ): ?NormalizedMessage {

        return collect(
            $this->parse($payload)
        )->first();
    }

    public function hasMessages(
        array $payload
    ): bool {

        return !empty($this->parse($payload));
    }

    public function statuses(
        array $payload
    ): array {

        $statuses = [];

        foreach (
            data_get($payload, 'entry', [])
            as $entry
        ) {

            foreach (
                data_get($entry, 'changes', [])
                as $change
            ) {

                foreach (
                    data_get(
                        $change,
                        'value.statuses',
                        []
                    )
                    as $status
                ) {

                    $statuses[] = $status;
                }
            }
        }

        return $statuses;
    }
}
