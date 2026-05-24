<?php

namespace Sejator\WabaSdk\DTO;

class PhoneNumber
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $displayPhoneNumber = null,
        public readonly ?string $verifiedName = null,
        public readonly ?string $status = null,
        public readonly ?string $qualityRating = null,
        public readonly ?string $qualityScore = null,
        public readonly ?string $platformType = null,
        public readonly ?string $codeVerificationStatus = null,
        public readonly ?string $throughput = null,
        public readonly array $metadata = [],
    ) {
        //
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) data_get(
                $data,
                'id'
            ),
            displayPhoneNumber: data_get(
                $data,
                'display_phone_number'
            ),
            verifiedName: data_get(
                $data,
                'verified_name'
            ),
            status: data_get(
                $data,
                'status'
            ),
            qualityRating: data_get(
                $data,
                'quality_rating'
            ),
            qualityScore: data_get(
                $data,
                'quality_score'
            ),
            platformType: data_get(
                $data,
                'platform_type'
            ),
            codeVerificationStatus: data_get(
                $data,
                'code_verification_status'
            ),
            throughput: data_get(
                $data,
                'throughput.level'
            ),
            metadata: data_get(
                $data,
                'metadata',
                []
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'display_phone_number' => $this->displayPhoneNumber,
            'verified_name' => $this->verifiedName,
            'status' => $this->status,
            'quality_rating' => $this->qualityRating,
            'quality_score' => $this->qualityScore,
            'platform_type' => $this->platformType,
            'code_verification_status' => $this->codeVerificationStatus,
            'throughput' => $this->throughput,
            'metadata' => $this->metadata,
        ];
    }
}
