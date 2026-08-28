<?php

namespace Sejator\WabaSdk\Services;

class CallService
{
    public function __construct(
        protected Client $client,
    ) {}

    /**
     * Balas SDP offer sebelum media benar-benar terhubung (opsional tapi
     * direkomendasikan Meta) - lihat accept() untuk konfirmasi final.
     * Retry di-nolkan & timeout dipersingkat - ini dipanggil dalam jendela
     * 30-60 detik dari Meta, tidak boleh molor karena retry bawaan.
     */
    public function preAccept(string $phoneNumberId, string $callId, string $sdpAnswer): array
    {
        return $this->post($phoneNumberId, [
            'call_id' => $callId,
            'action' => 'pre_accept',
            'session' => [
                'sdp_type' => 'answer',
                'sdp' => $sdpAnswer,
            ],
        ]);
    }

    public function accept(string $phoneNumberId, string $callId, string $sdpAnswer): array
    {
        return $this->post($phoneNumberId, [
            'call_id' => $callId,
            'action' => 'accept',
            'session' => [
                'sdp_type' => 'answer',
                'sdp' => $sdpAnswer,
            ],
        ]);
    }

    public function reject(string $phoneNumberId, string $callId): array
    {
        return $this->post($phoneNumberId, [
            'call_id' => $callId,
            'action' => 'reject',
        ]);
    }

    public function terminate(string $phoneNumberId, string $callId): array
    {
        return $this->post($phoneNumberId, [
            'call_id' => $callId,
            'action' => 'terminate',
        ]);
    }

    protected function post(string $phoneNumberId, array $payload): array
    {
        return $this->client
            ->withRetry(0)
            ->withTimeout(10)
            ->post(
                "{$phoneNumberId}/calls",
                array_merge([
                    'messaging_product' => 'whatsapp',
                ], $payload),
            );
    }
}
