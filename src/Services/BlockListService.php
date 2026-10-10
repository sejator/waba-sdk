<?php

namespace Sejator\WabaSdk\Services;

class BlockListService
{
    public function __construct(
        protected Client $client,
    ) {}

    public function list(string $phoneNumberId, int $limit = 100): array
    {
        return $this->client->get(
            "{$phoneNumberId}/block_users",
            [
                'limit' => $limit,
            ],
        );
    }

    public function block(string $phoneNumberId, array $waIds): array
    {
        return $this->client->post(
            "{$phoneNumberId}/block_users",
            [
                'messaging_product' => 'whatsapp',
                'block_users' => $this->mapUsers($waIds),
            ],
        );
    }

    public function unblock(string $phoneNumberId, array $waIds): array
    {
        return $this->client->delete(
            "{$phoneNumberId}/block_users",
            [
                'messaging_product' => 'whatsapp',
                'block_users' => $this->mapUsers($waIds),
            ],
        );
    }

    protected function mapUsers(array $waIds): array
    {
        return array_map(
            fn (string $waId) => ['user' => $waId],
            $waIds,
        );
    }
}
