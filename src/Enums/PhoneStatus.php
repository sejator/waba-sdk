<?php

namespace Sejator\WabaSdk\Enums;

enum PhoneStatus: string
{
    /*
    |--------------------------------------------------------------------------
    | Embedded / Cloud API
    |--------------------------------------------------------------------------
    */

    case CONNECTED = 'connected';

    case DISCONNECTED = 'disconnected';

    case PENDING = 'pending';

    case PENDING_REVIEW = 'pending_review';

    case REJECTED = 'rejected';

    case VERIFIED = 'verified';

    case UNVERIFIED = 'unverified';

        /*
    |--------------------------------------------------------------------------
    | Meta Phone Status
    |--------------------------------------------------------------------------
    */

    case ACTIVE = 'ACTIVE';

    case INACTIVE = 'INACTIVE';

    case FLAGGED = 'FLAGGED';

    case RESTRICTED = 'RESTRICTED';

    public function label(): string
    {
        return match ($this) {

            self::CONNECTED => 'Connected',

            self::DISCONNECTED => 'Disconnected',

            self::PENDING => 'Pending',

            self::PENDING_REVIEW => 'Pending Review',

            self::REJECTED => 'Rejected',

            self::VERIFIED => 'Verified',

            self::UNVERIFIED => 'Unverified',

            self::ACTIVE => 'Active',

            self::INACTIVE => 'Inactive',

            self::FLAGGED => 'Flagged',

            self::RESTRICTED => 'Restricted',
        };
    }

    public function isConnected(): bool
    {
        return in_array($this, [

            self::CONNECTED,

            self::ACTIVE,

            self::VERIFIED,

        ]);
    }

    public function isPending(): bool
    {
        return in_array($this, [

            self::PENDING,

            self::PENDING_REVIEW,

        ]);
    }
}
