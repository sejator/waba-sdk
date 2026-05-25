<?php

namespace Sejator\WabaSdk\DTO;

class EmbeddedSignupResult
{
    public function __construct(
        public readonly string $status,
        public readonly string $accessToken,
        public readonly ?WabaAccount $waba = null,
        public readonly ?PhoneNumber $phone = null,
        public readonly array $phoneNumbers = [],
        public readonly ?array $webhookSubscription = null,
        public readonly ?array $phoneRegistration = null,
        public readonly array $metadata = [],
        public readonly array $payload = [],
    ) {
        //
    }

    public static function fromArray(array $data): self
    {
        $phones = collect(
            data_get(
                $data,
                'phone_numbers',
                data_get(
                    $data,
                    'payload.phone_numbers',
                    []
                )
            )
        )->map(
            fn(array $phone) => PhoneNumber::fromArray(
                $phone
            )
        )->values()->all();

        $primaryPhone = !empty($phones)
            ? $phones[0]
            : null;

        $waba = null;

        if (data_get($data,  'waba_id')) {
            $waba = WabaAccount::fromArray([
                'id' => data_get(
                    $data,
                    'waba_id'
                ),
                'name' => data_get(
                    $data,
                    'waba_name'
                ),
                'currency' => data_get(
                    $data,
                    'currency'
                ),
                'timezone_id' => data_get(
                    $data,
                    'timezone_id'
                ),
                'business_id' => data_get(
                    $data,
                    'business_id'
                ),
                'business_name' => data_get(
                    $data,
                    'business_name'
                ),
            ]);
        }

        if (
            !$primaryPhone
            && data_get(
                $data,
                'phone_number_id'
            )
        ) {

            $primaryPhone = PhoneNumber::fromArray([
                'id' => data_get(
                    $data,
                    'phone_number_id'
                ),
                'display_phone_number' => data_get(
                    $data,
                    'display_phone_number'
                ),
            ]);

            $phones[] = $primaryPhone;
        }

        return new self(
            status: (string) data_get(
                $data,
                'status',
                'completed'
            ),
            accessToken: (string) data_get(
                $data,
                'access_token'
            ),
            waba: $waba,
            phone: $primaryPhone,
            phoneNumbers: $phones,
            webhookSubscription: data_get(
                $data,
                'webhook_subscription',
                data_get(
                    $data,
                    'payload.webhook_subscription'
                )
            ),
            phoneRegistration: data_get(
                $data,
                'phone_registration',
                data_get(
                    $data,
                    'payload.phone_registration'
                )
            ),
            metadata: data_get(
                $data,
                'metadata',
                data_get(
                    $data,
                    'payload.metadata',
                    []
                )
            ),
            payload: data_get(
                $data,
                'payload',
                []
            ),
        );
    }

    public function businessId(): ?string
    {
        return $this->waba?->businessId;
    }

    public function businessName(): ?string
    {
        return $this->waba?->businessName;
    }

    public function wabaId(): ?string
    {
        return $this->waba?->id;
    }

    public function wabaName(): ?string
    {
        return $this->waba?->name;
    }

    public function phoneNumberId(): ?string
    {
        return $this->phone?->id;
    }

    public function displayPhoneNumber(): ?string
    {
        return $this->phone?->displayPhoneNumber;
    }

    public function webhookSubscribed(): bool
    {
        return (bool) data_get(
            $this->webhookSubscription,
            'success',
            false
        );
    }

    public function phoneRegistered(): bool
    {
        return (bool) data_get(
            $this->phoneRegistration,
            'success',
            false
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'access_token' => $this->accessToken,
            'waba_id' => $this->waba?->id,
            'waba_name' => $this->waba?->name,
            'business_id' => $this->waba?->businessId,
            'business_name' => $this->waba?->businessName,
            'phone_number_id' => $this->phone?->id,
            'display_phone_number' => $this->phone?->displayPhoneNumber,
            'waba' => $this->waba?->toArray(),
            'phone' => $this->phone?->toArray(),
            'phone_numbers' => collect(
                $this->phoneNumbers
            )->map(
                fn(PhoneNumber $phone)
                => $phone->toArray()
            )->values()->all(),
            'webhook_subscription' => $this->webhookSubscription,
            'phone_registration' => $this->phoneRegistration,
            'metadata' => $this->metadata,
            'payload' => $this->payload,
        ];
    }
}
