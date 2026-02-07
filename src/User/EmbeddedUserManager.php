<?php

namespace Sejator\WabaSdk\User;

use Sejator\WabaSdk\Http\WabaClient;
use Sejator\WabaSdk\Exceptions\WabaException;

class EmbeddedUserManager
{
    public function __construct(
        protected WabaClient $client
    ) {}

    /**
     * Subscribe app ke WABA
     */
    public function subscribeAppToWaba(string $wabaId): bool
    {
        $res = $this->client->post("{$wabaId}/subscribed_apps");

        if (!($res['success'] ?? false)) {
            throw new WabaException('Failed to subscribe app to WABA');
        }

        return true;
    }

    /**
     * Assign system user ke WABA
     */
    public function assignSystemUserToWaba(
        string $wabaId,
        string $systemUserId,
        array $tasks = ['MANAGE']
    ): bool {
        if (!$systemUserId) {
            throw new WabaException('system user id missing');
        }

        $this->client->post(
            "{$wabaId}/assigned_users",
            [],
            [
                'user'  => $systemUserId,
                'tasks' => $tasks,
            ]
        );

        return true;
    }
}
