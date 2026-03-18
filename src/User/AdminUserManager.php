<?php

namespace Sejator\WabaSdk\User;

use Illuminate\Http\Client\Factory as HttpFactory;
use Sejator\WabaSdk\Exceptions\WabaException;

class AdminUserManager
{
    protected string $graph;
    protected string $appId;
    protected string $appToken;

    protected HttpFactory $http;

    public function __construct(HttpFactory $http)
    {
        $this->http = $http;

        $this->graph = rtrim(config('waba.meta.graph.base_url'), '/')
            . '/' . config('waba.meta.graph.version');

        $this->appId = config('waba.meta.app_id');

        $this->appToken =
            config('waba.meta.app_id') . '|' .
            config('waba.meta.app_secret');

        if (!$this->appId || !$this->appToken) {
            throw new WabaException('Meta app credentials not configured');
        }
    }

    public function createSystemUser(
        string $businessId,
        string $name = 'WABA System User'
    ): string {
        $this->validateId($businessId, 'businessId');

        $res = $this->request()->post(
            "{$this->graph}/{$businessId}/system_users",
            ['name' => $name]
        );

        return $res->json('id');
    }

    public function assignWabaAsset(
        string $userId,
        string $wabaId
    ): bool {
        $this->validateId($userId, 'userId');
        $this->validateId($wabaId, 'wabaId');

        $this->request()->post(
            "{$this->graph}/{$userId}/assigned_assets",
            [
                'asset' => $wabaId,
                'role'  => 'ADMIN',
            ]
        );

        return true;
    }

    public function generateToken(string $userId): UserToken
    {
        $this->validateId($userId, 'userId');

        $res = $this->request()->post(
            "{$this->graph}/{$userId}/access_tokens",
            [
                'app_id' => $this->appId,
                'scope'  => [
                    'whatsapp_business_management',
                    'whatsapp_business_messaging',
                ],
            ]
        );

        return new UserToken(
            $userId,
            $res->json('access_token')
        );
    }

    protected function request()
    {
        return $this->http
            ->withToken($this->appToken)
            ->timeout(config('waba.http.timeout', 10))
            ->retry(3, 500);
    }

    protected function validateId(string $value, string $field): void
    {
        if (empty($value)) {
            throw new WabaException("{$field} is required");
        }
    }
}
