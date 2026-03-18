<?php

namespace Sejator\WabaSdk;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Client\Factory as HttpFactory;
use Sejator\WabaSdk\Http\WabaClient;

class WabaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/waba.php',
            'waba'
        );

        $this->app->singleton(WabaClient::class, function ($app) {
            return new WabaClient(
                config('waba.token'),
                $app->make(HttpFactory::class)
            );
        });

        $this->app->singleton(WabaManager::class, function ($app) {
            return new WabaManager(
                $app->make(WabaClient::class)
            );
        });

        $this->app->alias(WabaManager::class, 'waba');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/waba.php' => config_path('waba.php'),
        ], 'waba-config');
    }
}
