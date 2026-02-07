<?php

namespace Sejator\WabaSdk\Utils;

use RuntimeException;

class Phone
{
    /**
     * Normalisasi nomor telepon ke format E.164 (tanpa tanda +).
     *
     * @param string      $phone     Nomor telepon input
     * @param string|null $countryCode Kode negara default (contoh: 62, 60, 1)
     *
     * @return string
     */
    public static function normalize(string $phone, ?string $countryCode = null): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($phone)) {
            throw new RuntimeException('Nomor telepon tidak valid');
        }

        // sudah oke format internasional
        if (!str_starts_with($phone, '0')) {
            return $phone;
        }

        // Khusus Indonesia
        if (str_starts_with($phone, '08')) {
            return '62' . substr($phone, 1);
        }

        if (!$countryCode) {
            throw new RuntimeException(
                'Nomor lokal non-Indonesia memerlukan country code'
            );
        }

        return $countryCode . substr($phone, 1);
    }

    /**
     * Normalisasi nomor telepon dengan country code eksplisit.
     *
     * @param string $phone
     * @param string $countryCode
     * @return string
     */
    public static function withCountry(string $phone, string $countryCode): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $countryCode = preg_replace('/[^0-9]/', '', $countryCode);

        if (empty($phone) || empty($countryCode)) {
            throw new RuntimeException(
                'Nomor telepon atau country code tidak valid'
            );
        }

        // sesuai fromat E.164
        if (str_starts_with($phone, $countryCode)) {
            return $phone;
        }

        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        return $countryCode . $phone;
    }
}
