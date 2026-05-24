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
        $response = $this->client
            ->system()
            ->get('/debug_token', [
                'input_token' => $accessToken,
            ]);

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
                return $targetIds[0];
            }
        }

        throw new RuntimeException(
            'Unable to resolve WABA ID.'
        );
    }

    public function shared(): array
    {
        return $this->client
            ->system()
            ->get(sprintf(
                '/%s/client_whatsapp_business_accounts',
                config('waba.meta.business_id')
            ));
    }

    public function owned(): array
    {
        return $this->client
            ->system()
            ->get(sprintf(
                '/%s/owned_whatsapp_business_accounts',
                config('waba.meta.business_id')
            ));
    }

    public function reviewStatus(
        string $wabaId,
    ): array {

        return $this->client
            ->system()
            ->get("/{$wabaId}", [
                'fields' =>
                'account_review_status',
            ]);
    }
}
