<?php

namespace Sejator\WabaSdk\Support;

use TemplateComponentNormalizer;

class ComponentNormalizer
{
    public function normalize(array $components): array
    {
        return app(
            TemplateComponentNormalizer::class
        )->normalize($components);
    }

    public function normalizeMessage(
        array $payload,
        array $options = []
    ): ?array {
        $type = $payload['type'] ?? null;

        return match ($type) {

            'template' =>
            $this->normalizeTemplate(
                $payload,
                $options['template'] ?? [],
            ),

            'image' =>
            $this->normalizeImage($payload),

            'video' =>
            $this->normalizeVideo($payload),

            'document' =>
            $this->normalizeDocument($payload),

            'interactive' =>
            $this->normalizeInteractive($payload),

            'button' =>
            $this->normalizeButton($payload),

            'location' =>
            $this->normalizeLocation($payload),

            'contacts' =>
            $this->normalizeContacts($payload),

            default => [],
        };
    }


    protected function normalizeImage(
        array $payload
    ): array {

        $image = $payload['image'] ?? [];

        return [
            'type' => 'image',
            'caption' => $image['caption'] ?? null,
        ];
    }

    protected function normalizeVideo(
        array $payload
    ): array {

        $video = $payload['video'] ?? [];

        return [
            'type' => 'video',
            'caption' => $video['caption'] ?? null,
        ];
    }

    protected function normalizeDocument(
        array $payload
    ): array {

        $document = $payload['document'] ?? [];

        return [
            'type' => 'document',
            'caption' => $document['caption'] ?? null,
            'filename' => $document['filename'] ?? null,
        ];
    }

    protected function normalizeInteractive(
        array $payload
    ): array {

        $interactive = $payload['interactive'] ?? [];

        $type = $interactive['type'] ?? null;

        if (
            in_array(
                $type,
                ['button_reply', 'list_reply']
            )
        ) {

            $reply = $interactive[$type] ?? [];

            return [
                'type' => $type,
                'id' => $reply['id'] ?? null,
                'title' => $reply['title'] ?? null,
                'description' => $reply['description'] ?? null,
            ];
        }

        return [
            'type' => $type,
            'header' => $interactive['header'] ?? null,
            'body' => data_get(
                $interactive,
                'body.text'
            ),
            'footer' => data_get(
                $interactive,
                'footer.text'
            ),
            'button' => data_get(
                $interactive,
                'action.button'
            ),
            'buttons' => collect(
                data_get(
                    $interactive,
                    'action.buttons',
                    []
                )
            )
                ->map(fn($button) => [
                    'id' => data_get(
                        $button,
                        'reply.id'
                    ),

                    'title' => data_get(
                        $button,
                        'reply.title'
                    ),
                ])
                ->values()
                ->toArray(),

            'sections' => data_get(
                $interactive,
                'action.sections'
            ),
        ];
    }

    protected function normalizeTemplate(
        array $payload,
        array $templateMeta = []
    ): array {

        $template = $payload['template'] ?? [];

        $components = collect(
            $template['components'] ?? []
        );

        $footer = $templateMeta['footer'] ?? null;
        $templateButtons = $templateMeta['buttons'] ?? [];

        $header = null;
        $body = [];
        $buttons = [];

        foreach ($components as $component) {

            $componentType = strtolower(
                $component['type'] ?? ''
            );

            if ($componentType === 'header') {
                $parameter = $component['parameters'][0] ?? null;

                if ($parameter) {

                    $header = [
                        'type' => $parameter['type'] ?? null,
                        'image' => data_get(
                            $parameter,
                            'image.link'
                        ),
                        'video' => data_get(
                            $parameter,
                            'video.link'
                        ),
                        'document' => data_get(
                            $parameter,
                            'document.link'
                        ),
                        'text' => data_get(
                            $parameter,
                            'text'
                        ),
                    ];
                }
            }

            if ($componentType === 'body') {

                $body = collect(
                    $component['parameters'] ?? []
                )
                    ->map(fn($parameter) => [
                        'type' => $parameter['type'] ?? null,
                        'text' => $parameter['text'] ?? null,
                    ])
                    ->values()
                    ->toArray();
            }
        }

        $buttons = collect($templateButtons)
            ->map(function ($button, $index) use ($components) {

                $runtimeButton = $components
                    ->firstWhere('index', $index);

                $parameter = data_get(
                    $runtimeButton,
                    'parameters.0',
                    []
                );

                return [
                    'type' => $button['type'] ?? null,
                    'text' => $button['text'] ?? null,
                    'sub_type' => strtolower(
                        $button['type'] ?? ''
                    ),
                    'index' => $index,
                    'value' => data_get(
                        $parameter,
                        'text'
                    ),
                    'url' => $button['url'] ?? null,
                    'phone_number' => $button['phone_number'] ?? null,
                    'flow_id' => $button['flow_id'] ?? null,
                ];
            })
            ->values()
            ->toArray();

        return [
            'type' => 'template',
            'name' => $template['name'] ?? null,
            'language' => data_get(
                $template,
                'language.code'
            ),
            'header' => $header,
            'body' => $body,
            'footer' => $footer,
            'buttons' => $buttons,
        ];
    }

    protected function normalizeButton(
        array $payload
    ): array {

        return [
            'type' => 'button',
            'text' => data_get(
                $payload,
                'button.text'
            ),
            'payload' => data_get(
                $payload,
                'button.payload'
            ),
        ];
    }

    protected function normalizeLocation(
        array $payload
    ): array {

        return [
            'type' => 'location',
            'name' => data_get(
                $payload,
                'location.name'
            ),
            'address' => data_get(
                $payload,
                'location.address'
            ),
            'latitude' => data_get(
                $payload,
                'location.latitude'
            ),
            'longitude' => data_get(
                $payload,
                'location.longitude'
            ),
        ];
    }

    protected function normalizeContacts(
        array $payload
    ): array {

        return [
            'type' => 'contacts',
            'contacts' => data_get(
                $payload,
                'contacts',
                []
            ),
        ];
    }
}
