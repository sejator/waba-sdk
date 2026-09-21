<?php

namespace Sejator\WabaSdk\Services;

class BusinessService
{
    public function __construct(
        protected Client $client,
    ) {}

    public function waba(string $wabaId, array $fields = []): array
    {
        if (empty($fields)) {
            $fields = [
                'id',
                'name',
                'currency',
                'timezone_id',
                'message_template_namespace',
                'owner_business_info',
                'account_review_status',
            ];
        }

        return $this->client->get(
            $wabaId,
            [
                'fields' => implode(
                    ',',
                    $fields
                ),
            ]
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

    /**
     * Status business verification portfolio pemilik WABA (VERIFIED,
     * NOT_VERIFIED, PENDING, REJECTED, dst) - null kalau Meta tidak
     * mengembalikannya.
     */
    public function businessVerificationStatus(string $wabaId): ?string
    {
        $response = $this->client->get(
            $wabaId,
            ['fields' => 'business_verification_status'],
        );

        return data_get($response, 'business_verification_status');
    }

    public function reviewStatus(string $wabaId): array
    {

        return $this->client->get(
            $wabaId,
            [
                'fields' =>
                'account_review_status',
            ]
        );
    }
}
