<?php

namespace Sejator\WabaSdk;

use Illuminate\Support\ServiceProvider;

class WabaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/waba.php',
            'waba'
        );

        $this->app->singleton('waba', function () {
            return new WabaManager();
        });

        $this->app->alias('waba', WabaManager::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/waba.php' => config_path('waba.php'),
        ], 'waba-config');
    }
}
