<?php

namespace Sejator\WabaSdk\Traits;

trait InteractsWithGraphApi
{
    protected function graphUrl(string $endpoint): string
    {
        return rtrim(config('waba.meta.base_url'), '/') . '/' . ltrim($endpoint, '/');
    }

    protected function token(): string
    {
        return config('waba.meta.access_token');
    }
}
