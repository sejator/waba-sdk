<?php

namespace Sejator\WabaSdk\Services;

use Illuminate\Support\Collection;
use Sejator\WabaSdk\Support\ComponentNormalizer;

class TemplateService
{

    public function __construct(
        protected Client $client,
    ) {}

    protected function endpoint(string $wabaId): string
    {
        return "/{$wabaId}/message_templates";
    }

    /**
     * Create template.
     *
     * @param array<string,mixed> $payload
     */
    public function create(string $wabaId, array $payload): array
    {

        $payload['category'] ??= 'UTILITY';
        $payload['language'] ??= 'en_US';

        if (!empty($payload['components'])) {

            $payload['components'] = $this->normalizeComponents(
                $payload['category'],
                $payload['components'],
            );
        }

        return $this->client->post(
            $this->endpoint($wabaId),
            $payload,
        );
    }

    /**
     * Get all templates.
     */
    public function all(string $wabaId, array $query = []): array
    {

        $query['limit'] ??= 100;

        if (!empty($query['fields'])) {
            $query['fields'] = $this->fields(
                $query['fields'],
            );
        }

        return $this->client->get(
            $this->endpoint($wabaId),
            $query,
        );
    }

    /**
     * Find template.
     */
    public function find(string $templateId, array $fields = []): array
    {

        $query = [];

        if (!empty($fields)) {
            $query['fields'] = $this->fields(
                $fields,
            );
        }

        return $this->client->get(
            "/{$templateId}",
            $query,
        );
    }

    /**
     * Delete template.
     */
    public function delete(string $wabaId, string $name): bool
    {

        $this->client->delete(
            $this->endpoint($wabaId),
            [
                'name' => $name,
            ],
        );

        return true;
    }

    /**
     * Sync templates.
     */
    public function sync(string $wabaId): array
    {
        return $this->all($wabaId);
    }

    /**
     * Check template exists.
     */
    public function exists(string $wabaId, string $name): bool
    {

        return $this->collection($wabaId)
            ->contains(
                fn(array $template) =>
                data_get($template, 'name') === $name,
            );
    }

    /**
     * Create template if not exists.
     *
     * @param array<string,mixed> $payload
     */
    public function createIfNotExists(string $wabaId, array $payload): array
    {

        $name = data_get(
            $payload,
            'name',
        );

        if (!$name) {

            return [
                'status' => 'error',
                'message' => 'Template name is required.',
            ];
        }

        if ($this->exists(
            $wabaId,
            $name,
        )) {

            return [
                'status' => 'exists',
                'message' => 'Template already exists.',
            ];
        }

        return $this->create(
            $wabaId,
            $payload,
        );
    }

    /**
     * Filter by category.
     */
    public function byCategory(string $wabaId, string $category): Collection
    {

        return $this->collection(
            $wabaId,
            [
                'category' => $category,
            ],
        );
    }

    /**
     * Approved templates.
     */
    public function approved(string $wabaId): Collection
    {

        return $this->collection(
            $wabaId,
            [
                'status' => 'APPROVED',
            ],
        );
    }

    /**
     * Template collection.
     */
    public function collection(string $wabaId, array $query = []): Collection
    {

        return collect(
            data_get(
                $this->all(
                    $wabaId,
                    $query,
                ),
                'data',
                [],
            ),
        );
    }

    /**
     * Normalize components before send to Meta.
     *
     * @param array<int,array<string,mixed>> $components
     * @return array<int,array<string,mixed>>
     */
    protected function normalizeComponents(string $category, array $components): array
    {

        return app(ComponentNormalizer::class)
            ->normalize(
                $category,
                $components,
            );
    }

    /**
     * Convert field array to Graph API fields.
     */
    protected function fields(array $fields): string
    {

        return implode(
            ',',
            $fields,
        );
    }
}
