<?php

namespace Sejator\WabaSdk\User;

use Illuminate\Support\Facades\Http;
use Sejator\WabaSdk\Exceptions\WabaException;

class AdminUserManager
{
    protected string $graph;
    protected string $appId;
    protected string $appToken;

    protected ?string $businessId = null;
    protected ?string $wabaId     = null;
    protected ?string $userId     = null;

    public function __construct()
    {
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

    public function forBusiness(string $businessId): self
    {
        $this->businessId = $businessId;
        return $this;
    }

    public function forWaba(string $wabaId): self
    {
        $this->wabaId = $wabaId;
        return $this;
    }

    public function createSystemUser(string $name = 'WABA System User'): self
    {
        if (!$this->businessId) {
            throw new WabaException('businessId is required');
        }

        $res = Http::withToken($this->appToken)->post(
            "{$this->graph}/{$this->businessId}/system_users",
            ['name' => $name]
        );

        if (!$res->successful()) {
            throw new WabaException('Failed to create system user');
        }

        $this->userId = $res->json('id');
        return $this;
    }

    public function assignWabaAsset(): self
    {
        if (!$this->userId || !$this->wabaId) {
            throw new WabaException('userId and wabaId are required');
        }

        $res = Http::withToken($this->appToken)->post(
            "{$this->graph}/{$this->userId}/assigned_assets",
            [
                'asset' => $this->wabaId,
                'role'  => 'ADMIN',
            ]
        );

        if (!$res->successful()) {
            throw new WabaException('Failed to assign WABA asset');
        }

        return $this;
    }

    public function generateToken(): UserToken
    {
        if (!$this->userId) {
            throw new WabaException('userId is required');
        }

        $res = Http::withToken($this->appToken)->post(
            "{$this->graph}/{$this->userId}/access_tokens",
            [
                'app_id' => $this->appId,
                'scope'  => [
                    'whatsapp_business_management',
                    'whatsapp_business_messaging',
                ],
            ]
        );

        if (!$res->successful()) {
            throw new WabaException('Failed to generate system user token');
        }

        return new UserToken(
            $this->userId,
            $res->json('access_token')
        );
    }
}
