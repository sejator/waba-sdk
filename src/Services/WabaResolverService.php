<?php

namespace Sejator\WabaSdk\Services;

use RuntimeException;

class WabaResolverService
{
    public function __construct(
        protected Client $client,
    ) {}

    /**
     * Resolve WABA From Embedded Token
     * Official Meta Flow:
     * GET /debug_token
     */
    public function resolveFromAccessToken(string $accessToken): string
    {
        $appId = config(
            'waba.meta.app_id'
        );

        $appSecret = config(
            'waba.meta.app_secret'
        );

        if (!$appId || !$appSecret) {

            throw new RuntimeException(
                'Meta app credentials are not configured.'
            );
        }

        $appAccessToken = "{$appId}|{$appSecret}";

        $response = $this->client
            ->withToken($appAccessToken)
            ->get(
                '/debug_token',
                [
                    'input_token' => $accessToken,
                    'access_token' => $appAccessToken,
                ]
            );

        $scopes = data_get(
            $response,
            'data.granular_scopes',
            []
        );

        foreach ($scopes as $scope) {

            if (data_get($scope, 'scope') !== 'whatsapp_business_management') {
                continue;
            }

            $targetIds = data_get(
                $scope,
                'target_ids',
                []
            );

            if (!empty($targetIds[0])) {

                return (string)
                $targetIds[0];
            }
        }

        $shared = $this->shared();

        $sharedWaba = collect(
            data_get(
                $shared,
                'data',
                []
            )
        )->first();

        if ($sharedWaba) {
            return (string) data_get(
                $sharedWaba,
                'id'
            );
        }

        throw new RuntimeException(
            'Unable to resolve WABA ID.'
        );
    }

    public function shared(): array
    {
        return $this->client
            ->system()
            ->get(
                sprintf(
                    '/%s/client_whatsapp_business_accounts',
                    config('waba.meta.business_id')
                )
            );
    }

    public function owned(): array
    {
        return $this->client
            ->system()
            ->get(
                sprintf(
                    '/%s/owned_whatsapp_business_accounts',
                    config('waba.meta.business_id')
                )
            );
    }

    public function reviewStatus(string $wabaId): array
    {

        return $this->client
            ->system()
            ->get(
                "/{$wabaId}",
                [
                    'fields' => 'account_review_status',
                ]
            );
    }
}
