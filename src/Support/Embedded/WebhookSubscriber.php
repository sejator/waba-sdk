<?php

namespace Sejator\WabaSdk\Support\Embedded;

use Sejator\WabaSdk\Services\Client;

class WebhookSubscriber
{
    public function __construct(
        protected Client $client,
    ) {}

    public function subscribe(string $wabaId): array
    {
        return $this->client->post(
            "{$wabaId}/subscribed_apps"
        );
    }

    public function subscribedApps(string $wabaId): array
    {
        return $this->client->get(
            "{$wabaId}/subscribed_apps"
        );
    }

    public function unsubscribe(string $wabaId): array
    {
        return $this->client->delete(
            "{$wabaId}/subscribed_apps"
        );
    }
}
