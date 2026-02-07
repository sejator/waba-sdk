<?php

namespace Sejator\WabaSdk\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Sejator\WabaSdk\Exceptions\WabaException;

class CloudAuth
{
    protected string $graphUrl;
    protected string $version;
    protected string $appId;
    protected string $appSecret;

    public function __construct()
    {
        $this->graphUrl  = rtrim(config('waba.meta.graph.base_url'), '/');
        $this->version   = config('waba.meta.graph.version');
        $this->appId     = config('waba.meta.app_id');
        $this->appSecret = config('waba.meta.app_secret');

        if (!$this->appId || !$this->appSecret) {
            throw new WabaException('Meta app credentials not configured');
        }
    }

    /**
     * ============================================================
     * Embedded Signup
     * NO redirect_uri
     * ============================================================
     */
    public function exchangeEmbeddedCode(string $code): array
    {
        return $this->exchange($code, null);
    }

    /**
     * ============================================================
     * Manual OAuth (Redirect-based)
     * WITH redirect_uri
     * ============================================================
     */
    public function exchangeOAuthCode(string $code, string $redirectUri): array
    {
        if (!filter_var($redirectUri, FILTER_VALIDATE_URL)) {
            throw new WabaException('Invalid redirect_uri');
        }

        return $this->exchange($code, $redirectUri);
    }

    /**
     * ============================================================
     * OAuth exchange logic
     * ============================================================
     */
    protected function exchange(string $code, ?string $redirectUri): array
    {
        $payload = [
            'client_id'     => $this->appId,
            'client_secret' => $this->appSecret,
            'code'          => $code,
        ];

        if ($redirectUri) {
            $payload['redirect_uri'] = $redirectUri;
        }

        $tokenRes = Http::asForm()->post(
            "{$this->graphUrl}/{$this->version}/oauth/access_token",
            $payload
        );

        if (!$tokenRes->successful()) {
            Log::error('META OAUTH TOKEN EXCHANGE FAILED', [
                'status' => $tokenRes->status(),
                'body'   => $tokenRes->json(),
            ]);
            throw new WabaException('Failed to exchange OAuth code');
        }

        $accessToken = $tokenRes->json('access_token');

        if (!$accessToken) {
            throw new WabaException('Access token missing from Meta response');
        }

        $debugRes = Http::get(
            "{$this->graphUrl}/{$this->version}/debug_token",
            [
                'input_token'  => $accessToken,
                'access_token' => "{$this->appId}|{$this->appSecret}",
            ]
        );

        if (!$debugRes->successful()) {
            throw new WabaException('Failed to debug Meta access token');
        }

        $data = $debugRes->json('data');

        if (empty($data['granular_scopes'])) {
            throw new WabaException('granular_scopes missing from Meta token');
        }

        $wabaId     = null;
        $phoneId    = null;
        $businessId = null;

        foreach ($data['granular_scopes'] as $scope) {

            if ($scope['scope'] === 'whatsapp_business_management') {
                $wabaId = $scope['target_ids'][0] ?? null;

                $businessId =
                    $scope['business_id']
                    ?? $scope['asset_id']
                    ?? null;
            }

            if ($scope['scope'] === 'whatsapp_business_messaging') {
                $phoneId = $scope['target_ids'][0] ?? null;
            }
        }

        if (!$wabaId || !$phoneId) {
            throw new WabaException(
                'WABA or phone_number_id not found in token scopes'
            );
        }

        if (!$businessId) {
            Log::warning('BUSINESS ID NOT FOUND IN GRANULAR SCOPES', [
                'scopes' => $data['granular_scopes'],
            ]);
        }

        $wabaName = null;

        $wabaRes = Http::withToken($accessToken)->get(
            "{$this->graphUrl}/{$this->version}/{$wabaId}",
            [
                'fields' => 'id,name',
            ]
        );

        if ($wabaRes->successful()) {
            $wabaName = $wabaRes->json('name');
        }

        $expiresAt = null;
        if (!empty($data['expires_at'])) {
            $expiresAt = now()->addSeconds(
                max(0, $data['expires_at'] - time())
            );
        }

        Log::info('WABA OAUTH EXCHANGE SUCCESS', [
            'waba_id'     => $wabaId,
            'phone_id'    => $phoneId,
            'business_id' => $businessId,
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
}
