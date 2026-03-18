<?php

namespace Sejator\WabaSdk\Endpoints;

use InvalidArgumentException;
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
        if (empty($payload)) {
            throw new InvalidArgumentException('payload is required');
        }

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
        if (!$name || !$language) {
            throw new InvalidArgumentException('name & language required');
        }

        return $this->client->delete(
            "{$this->wabaId}/message_templates",
            [
                'name' => $name,
                'language' => $language,
            ]
        );
    }
}
