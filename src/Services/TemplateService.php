<?php

namespace Sejator\WabaSdk\Services;

use Illuminate\Support\Collection;
use Sejator\WabaSdk\Support\ComponentNormalizer;

class TemplateService
{
    protected array $defaultFields = [
        'id',
        'name',
        'namespace',
        'language',
        'category',
        'status',
        'quality_score',
        'quality_rating',
        'review_status',
        'previous_category',
        'components',
        'parameter_format',
    ];

    public function __construct(
        protected Client $client
    ) {}

    protected function endpoint(string $wabaId): string
    {
        return "/{$wabaId}/message_templates";
    }

    public function create(string $wabaId, array $payload): array
    {

        if (!empty($payload['components'])) {
            $payload['components'] = $this->normalizeComponents(
                $payload['components']
            );
        }

        $payload['category'] ??= 'UTILITY';
        $payload['language'] ??= 'en_US';

        return $this->client->post(
            $this->endpoint($wabaId),
            $payload
        );
    }

    public function all(string $wabaId, array $query = []): array
    {
        $query['limit'] ??= 100;

        $query['fields'] ??= $this->fields();

        return $this->client->get(
            $this->endpoint($wabaId),
            $query
        );
    }

    public function collection(string $wabaId, array $query = []): Collection
    {
        return collect(
            data_get(
                $this->all(
                    $wabaId,
                    $query
                ),
                'data',
                []
            )
        );
    }

    public function find(string $templateId, array $fields = []): array
    {
        $fields = empty($fields)
            ? $this->defaultFields
            : $fields;

        return $this->client->get(
            "/{$templateId}",
            [
                'fields' => $this->fields($fields),
            ]
        );
    }

    public function delete(string $wabaId, string $name,): bool
    {
        $this->client->delete(
            $this->endpoint($wabaId),
            [
                'name' => $name,
            ]
        );

        return true;
    }

    public function sync(string $wabaId): array
    {
        return $this->all($wabaId);
    }

    public function exists(string $wabaId, string $name): bool
    {
        return $this->collection($wabaId)
            ->contains(
                fn(array $template) => data_get(
                    $template,
                    'name'
                ) === $name
            );
    }

    public function createIfNotExists(string $wabaId, array $payload): array
    {
        $name = data_get(
            $payload,
            'name'
        );

        if (!$name) {
            return [
                'status' => 'error',
                'message' => 'Template name is required.',
            ];
        }

        if ($this->exists($wabaId, $name)) {
            return [
                'status' => 'exists',
                'message' => 'Template already exists.',
            ];
        }

        return $this->create(
            $wabaId,
            $payload
        );
    }

    public function byCategory(string $wabaId, string $category): Collection
    {
        return $this->collection(
            $wabaId,
            [
                'category' => $category,
            ]
        );
    }

    public function approved(string $wabaId): Collection
    {
        return $this->collection(
            $wabaId,
            [
                'status' => 'APPROVED',
            ]
        );
    }

    protected function normalizeComponents(array $components): array
    {
        return app(ComponentNormalizer::class)->normalize(
            $components
        );
    }

    protected function fields(array $fields = []): string
    {
        return implode(
            ',',
            empty($fields)
                ? $this->defaultFields
                : $fields
        );
    }
}
