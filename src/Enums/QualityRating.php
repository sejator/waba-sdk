<?php

namespace Sejator\WabaSdk\Enums;

enum QualityRating: string
{
    case GREEN = 'GREEN';
    case YELLOW = 'YELLOW';
    case RED = 'RED';
    case UNKNOWN = 'UNKNOWN';

    public function label(): string
    {
        return match ($this) {
            self::GREEN => 'High Quality',
            self::YELLOW => 'Medium Quality',
            self::RED => 'Low Quality',
            self::UNKNOWN => 'Unknown',
        };
    }

    public function isHealthy(): bool
    {
        return $this === self::GREEN;
    }

    public function isWarning(): bool
    {
        return $this === self::YELLOW;
    }

    public function isCritical(): bool
    {
        return $this === self::RED;
    }
}
