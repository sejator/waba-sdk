<?php

namespace Sejator\WabaSdk\DTO;

class Template
{
    public function __construct(

        /*
        |--------------------------------------------------------------------------
        | Template ID
        |--------------------------------------------------------------------------
        */

        public readonly ?string $id = null,

        /*
        |--------------------------------------------------------------------------
        | Template Name
        |--------------------------------------------------------------------------
        */

        public readonly string $name,

        /*
        |--------------------------------------------------------------------------
        | Category
        |--------------------------------------------------------------------------
        */

        public readonly ?string $category = null,

        /*
        |--------------------------------------------------------------------------
        | Language
        |--------------------------------------------------------------------------
        */

        public readonly ?string $language = null,

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        public readonly ?string $status = null,

        /*
        |--------------------------------------------------------------------------
        | Components
        |--------------------------------------------------------------------------
        */

        public readonly array $components = [],

        /*
        |--------------------------------------------------------------------------
        | Raw Payload
        |--------------------------------------------------------------------------
        */

        public readonly array $payload = [],

    ) {}

    public static function fromArray(array $data): self
    {
        return new self(

            id: data_get($data, 'id'),

            name: (string) data_get($data, 'name'),

            category: data_get($data, 'category'),

            language: data_get($data, 'language'),

            status: data_get($data, 'status'),

            components: data_get(
                $data,
                'components',
                []
            ),

            payload: $data,

        );
    }

    public function toArray(): array
    {
        return [

            'id' => $this->id,

            'name' => $this->name,

            'category' => $this->category,

            'language' => $this->language,

            'status' => $this->status,

            'components' => $this->components,

            'payload' => $this->payload,

        ];
    }
}
