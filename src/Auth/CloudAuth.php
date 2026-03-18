<?php

namespace Sejator\WabaSdk\Auth;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Log;
use Sejator\WabaSdk\Exceptions\WabaException;

class CloudAuth
{
    protected string $graphUrl;
    protected string $version;
    protected string $appId;
    protected string $appSecret;

    protected HttpFactory $http;

    public function __construct(HttpFactory $http)
    {
        $this->http = $http;

        $this->graphUrl  = rtrim(config('waba.meta.graph.base_url'), '/');
        $this->version   = config('waba.meta.graph.version');
        $this->appId     = config('waba.meta.app_id');
        $this->appSecret = config('waba.meta.app_secret');

        if (!$this->appId || !$this->appSecret) {
            throw new WabaException('Meta app credentials not configured');
        }
    }

    public function exchangeEmbeddedCode(string $code): array
    {
        return $this->exchange($code, null);
    }

    public function exchangeOAuthCode(string $code, string $redirectUri): array
    {
        if (!filter_var($redirectUri, FILTER_VALIDATE_URL)) {
            throw new WabaException('Invalid redirect_uri');
        }

        return $this->exchange($code, $redirectUri);
    }

    protected function exchange(string $code, ?string $redirectUri): array
    {
        $payload = [
            'client_id'     => $this->appId,
            'client_secret' => $this->appSecret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
        ];

        if ($redirectUri) {
            $payload['redirect_uri'] = $redirectUri;
        }

        $http = $this->http
            ->asForm()
            ->timeout(config('waba.http.timeout', 10))
            ->retry(3, 500);

        $tokenRes = $http->post(
            "{$this->graphUrl}/{$this->version}/oauth/access_token",
            $payload
        );

        if (!$tokenRes->successful()) {
            $this->logMetaError('TOKEN_EXCHANGE_FAILED', $tokenRes);

            throw new WabaException(
                $tokenRes->json('error.message') ?? 'Failed to exchange OAuth code'
            );
        }

        $accessToken = $tokenRes->json('access_token');

        if (!$accessToken) {
            throw new WabaException('Access token missing from Meta response');
        }

        $debugRes = $http->get(
            "{$this->graphUrl}/{$this->version}/debug_token",
            [
                'input_token'  => $accessToken,
                'access_token' => "{$this->appId}|{$this->appSecret}",
            ]
        );

        if (!$debugRes->successful()) {
            $this->logMetaError('DEBUG_TOKEN_FAILED', $debugRes);
            throw new WabaException('Failed to debug Meta access token');
        }

        $data = $debugRes->json('data');

        if (empty($data['granular_scopes'])) {
            throw new WabaException('granular_scopes missing from Meta token');
        }

        [$wabaId, $phoneId, $businessId] = $this->extractScopes($data['granular_scopes']);

        if (!$wabaId || !$phoneId) {
            throw new WabaException('WABA or phone_number_id not found in token scopes');
        }

        $wabaName = $this->fetchWabaName($accessToken, $wabaId);

        $expiresAt = $this->resolveExpiry($data);

        Log::info('WABA OAUTH SUCCESS', [
            'waba_id'  => $wabaId,
            'phone_id' => $phoneId,
        ]);

        return [
            'access_token'     => $accessToken,
            'token_expires_at' => $expiresAt,
            'waba_id'          => (string) $wabaId,
            'phone_number_id'  => (string) $phoneId,
            'business_id'      => (string) $businessId,
            'waba_name'        => $wabaName,
        ];
    }

    protected function extractScopes(array $scopes): array
    {
        $wabaId = $phoneId = $businessId = null;

        foreach ($scopes as $scope) {

            if ($scope['scope'] === 'whatsapp_business_management') {
                $wabaId = $scope['target_ids'][0] ?? null;
                $businessId = $scope['business_id'] ?? $scope['asset_id'] ?? null;
            }

            if ($scope['scope'] === 'whatsapp_business_messaging') {
                $phoneId = $scope['target_ids'][0] ?? null;
            }
        }

        return [$wabaId, $phoneId, $businessId];
    }

    protected function fetchWabaName(string $token, string $wabaId): ?string
    {
        $res = $this->http
            ->withToken($token)
            ->get("{$this->graphUrl}/{$this->version}/{$wabaId}", [
                'fields' => 'id,name',
            ]);

        return $res->successful() ? $res->json('name') : null;
    }

    protected function resolveExpiry(array $data): ?\Carbon\Carbon
    {
        if (empty($data['expires_at'])) {
            return null;
        }

        return now()->addSeconds(
            max(0, $data['expires_at'] - time())
        );
    }

    protected function logMetaError(string $context, $response): void
    {
        Log::error("META {$context}", [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);
    }
}
