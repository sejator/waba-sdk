<?php

namespace Sejator\WabaSdk\Enums;

enum WebhookField: string
{
    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    case MESSAGES = 'messages';

    case MESSAGE_TEMPLATE_STATUS_UPDATE =
    'message_template_status_update';

        /*
    |--------------------------------------------------------------------------
    | Phone Number
    |--------------------------------------------------------------------------
    */

    case PHONE_NUMBER_NAME_UPDATE =
    'phone_number_name_update';

    case PHONE_NUMBER_QUALITY_UPDATE =
    'phone_number_quality_update';

        /*
    |--------------------------------------------------------------------------
    | Account
    |--------------------------------------------------------------------------
    */

    case ACCOUNT_UPDATE = 'account_update';

    case ACCOUNT_REVIEW_UPDATE =
    'account_review_update';

        /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    case SECURITY = 'security';

    public function label(): string
    {
        return match ($this) {

            self::MESSAGES =>
            'Incoming Messages',

            self::MESSAGE_TEMPLATE_STATUS_UPDATE =>
            'Template Status Update',

            self::PHONE_NUMBER_NAME_UPDATE =>
            'Phone Number Name Update',

            self::PHONE_NUMBER_QUALITY_UPDATE =>
            'Phone Quality Update',

            self::ACCOUNT_UPDATE =>
            'Account Update',

            self::ACCOUNT_REVIEW_UPDATE =>
            'Account Review Update',

            self::SECURITY =>
            'Security',
        };
    }

    public static function defaults(): array
    {
        return [

            self::MESSAGES,

            self::MESSAGE_TEMPLATE_STATUS_UPDATE,

            self::PHONE_NUMBER_NAME_UPDATE,

            self::PHONE_NUMBER_QUALITY_UPDATE,

        ];
    }

    public static function values(): array
    {
        return array_map(

            fn(self $field) => $field->value,

            self::cases()

        );
    }
}
