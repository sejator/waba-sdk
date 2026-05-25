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
                []
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
            metadata: data_get(
                $data,
                'metadata',
                []
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
            'metadata' => $this->metadata,
            'payload' => $this->payload,
        ];
    }
}
