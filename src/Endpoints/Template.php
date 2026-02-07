<?php

namespace Sejator\WabaSdk\Endpoints;

use Sejator\WabaSdk\Http\WabaClient;

class Template
{
    public function __construct(
        protected WabaClient $client,
        protected string $wabaId
    ) {}

    /**
     * List templates
     */
    public function list(array $params = []): array
    {
        return $this->client->get(
            "{$this->wabaId}/message_templates",
            $params
        );
    }

    /**
     * Create template
     * Payload mengikuti Postman Collection Meta
     */
    public function create(array $payload): array
    {
        return $this->client->post(
            "{$this->wabaId}/message_templates",
            $payload
        );
    }

    /**
     * Delete template
     */
    public function delete(string $name, string $language): array
    {
        return $this->client->delete(
            "{$this->wabaId}/message_templates",
            [
                'name' => $name,
                'language' => $language,
            ]
        );
    }
}
