<?php

namespace Sejator\WabaSdk\Facades;

use Illuminate\Support\Facades\Facade;

class Waba extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'waba';
    }
}
