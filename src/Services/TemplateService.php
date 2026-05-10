<?php

namespace Sejator\WabaSdk\Services;

use Sejator\WabaSdk\Support\ComponentNormalizer;

class TemplateService
{
    public function __construct(
        protected Client $client,
        protected ComponentNormalizer $normalizer
    ) {}

    protected function endpoint(string $wabaId): string
    {
        return "/{$wabaId}/message_templates";
    }

    /**
     * Create Template
     */
    public function create(string $wabaId, array $payload): array
    {
        $payload['components'] = $this->normalizer->normalize(
            $payload['components'] ?? []
        );

        return $this->client->post($this->endpoint($wabaId), $payload);
    }

    /**
     * Get all templates
     */
    public function all(string $wabaId): array
    {
        return $this->client->get($this->endpoint($wabaId));
    }

    /**
     * Get detail template
     */
    public function detail(string $templateId): array
    {
        return $this->client->get("/{$templateId}");
    }

    /**
     * Delete template by name
     */
    public function delete(string $wabaId, string $name): bool
    {
        $endpoint = $this->endpoint($wabaId) . '?name=' . urlencode($name);

        $this->client->delete($endpoint);

        return true;
    }

    /**
     * Sync templates
     */
    public function sync(string $wabaId): array
    {
        return $this->all($wabaId);
    }

    /**
     * Check template exists by name
     */
    public function exists(string $wabaId, string $name): bool
    {
        $templates = $this->all($wabaId);

        return collect(data_get($templates, 'data', []))
            ->contains(fn($tpl) => $tpl['name'] === $name);
    }

    /**
     * Create or update
     */
    public function createIfNotExists(string $wabaId, array $payload): array
    {
        if ($this->exists($wabaId, $payload['name'])) {
            return [
                'status' => 'exists',
                'message' => 'Template already exists'
            ];
        }

        return $this->create($wabaId, $payload);
    }
}
