<?php

namespace Sejator\WabaSdk\User;

use InvalidArgumentException;
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
        $this->validateId($wabaId, 'wabaId');

        $res = $this->client->post("{$wabaId}/subscribed_apps");

        if (!($res['success'] ?? false)) {
            throw new WabaException(
                $res['error']['message'] ?? 'Failed to subscribe app to WABA'
            );
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
        $this->validateId($wabaId, 'wabaId');
        $this->validateId($systemUserId, 'systemUserId');

        if (empty($tasks)) {
            throw new InvalidArgumentException('tasks cannot be empty');
        }

        $this->client->post(
            "{$wabaId}/assigned_users",
            [
                'user'  => $systemUserId,
                'tasks' => $tasks,
            ]
        );

        return true;
    }

    /**
     * (Optional) Remove system user dari WABA
     */
    public function removeSystemUserFromWaba(
        string $wabaId,
        string $systemUserId
    ): bool {
        $this->validateId($wabaId, 'wabaId');
        $this->validateId($systemUserId, 'systemUserId');

        $this->client->delete(
            "{$wabaId}/assigned_users",
            [
                'user' => $systemUserId,
            ]
        );

        return true;
    }

    /**
     * (Optional) List assigned users (debugging SaaS)
     */
    public function listAssignedUsers(string $wabaId): array
    {
        $this->validateId($wabaId, 'wabaId');

        return $this->client->get(
            "{$wabaId}/assigned_users",
            ['fields' => 'id,name,tasks']
        );
    }

    /**
     * Basic validation helper
     */
    protected function validateId(string $value, string $field): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException("{$field} is required");
        }
    }
}
