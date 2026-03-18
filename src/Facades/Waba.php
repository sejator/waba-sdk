<?php

namespace Sejator\WabaSdk\Facades;

use Illuminate\Support\Facades\Facade;
use Sejator\WabaSdk\WabaManager;

/**
 * @method static \Sejator\WabaSdk\WabaManager withAccessToken(string $token)
 * @method static \Sejator\WabaSdk\Endpoints\Message messages(string $phoneNumberId)
 * @method static \Sejator\WabaSdk\Endpoints\Template templates(string $wabaId)
 * @method static \Sejator\WabaSdk\Endpoints\Media media()
 * @method static \Sejator\WabaSdk\Endpoints\PhoneNumber phoneNumbers()
 * @method static \Sejator\WabaSdk\Billing\CreditManager credit()
 * @method static \Sejator\WabaSdk\Auth\EmbeddedSignup embeddedSignup()
 * @method static array exchangeEmbeddedCode(string $code)
 * @method static array exchangeOAuthCode(string $code, string $redirectUri)
 */
class Waba extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'waba';
    }

    protected static function getFacadeRoot(): WabaManager
    {
        return parent::getFacadeRoot();
    }
}
