<?php

namespace Sejator\WabaSdk;

use Illuminate\Support\ServiceProvider;
use Sejator\WabaSdk\Contracts\StateStore;
use Sejator\WabaSdk\Contracts\TokenStore;
use Sejator\WabaSdk\Services\BusinessService;
use Sejator\WabaSdk\Services\Client;
use Sejator\WabaSdk\Services\EmbeddedSignupService;
use Sejator\WabaSdk\Services\MessageService;
use Sejator\WabaSdk\Services\PhoneNumberService;
use Sejator\WabaSdk\Services\TemplateService;
use Sejator\WabaSdk\Services\WabaResolverService;
use Sejator\WabaSdk\Support\Auth\CacheTokenStore;
use Sejator\WabaSdk\Support\ComponentNormalizer;
use Sejator\WabaSdk\Support\Embedded\CacheStateStore;
use Sejator\WabaSdk\Support\Embedded\OAuthCallbackHandler;
use Sejator\WabaSdk\Support\Embedded\OAuthUrlGenerator;
use Sejator\WabaSdk\Support\Embedded\TokenExchanger;
use Sejator\WabaSdk\Support\Embedded\WebhookSubscriber;
use Sejator\WabaSdk\Utils\Phone;

class WabaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/waba.php',
            'waba'
        );

        $this->app->bind(
            Client::class,
            fn() => new Client(
                config('waba.meta.base_url')
            )
        );

        $this->app->singleton(
            TokenStore::class,
            fn() => new CacheTokenStore()
        );

        $this->app->singleton(
            StateStore::class,
            fn() => new CacheStateStore()
        );

        $this->app->singleton(
            BusinessService::class,
            fn($app) => new BusinessService(
                $app->make(Client::class)
            )
        );

        $this->app->singleton(
            PhoneNumberService::class,
            fn($app) => new PhoneNumberService(
                $app->make(Client::class)
            )
        );

        $this->app->singleton(
            TemplateService::class,
            fn($app) => new TemplateService(
                $app->make(Client::class)
            )
        );

        $this->app->singleton(
            MessageService::class,
            fn($app) => new MessageService(
                $app->make(Client::class)
            )
        );

        $this->app->singleton(
            WabaResolverService::class,
            fn($app) => new WabaResolverService(
                $app->make(Client::class)
            )
        );

        $this->app->singleton(
            OAuthUrlGenerator::class
        );

        $this->app->singleton(
            TokenExchanger::class,
            fn($app) => new TokenExchanger(
                $app->make(Client::class)
            )
        );

        $this->app->singleton(
            WebhookSubscriber::class,
            fn($app) => new WebhookSubscriber(
                $app->make(Client::class)
            )
        );

        $this->app->singleton(
            OAuthCallbackHandler::class,
            fn($app) => new OAuthCallbackHandler(
                tokenExchanger: $app->make(TokenExchanger::class),
                states: $app->make(StateStore::class),
                tokens: $app->make(TokenStore::class),
                resolver: $app->make(WabaResolverService::class),
            )
        );

        $this->app->singleton(
            EmbeddedSignupService::class,
            fn($app) => new EmbeddedSignupService(
                client: $app->make(Client::class),
                urlGenerator: $app->make(OAuthUrlGenerator::class),
                tokenExchanger: $app->make(TokenExchanger::class),
                subscriber: $app->make(WebhookSubscriber::class),
                businesses: $app->make(BusinessService::class),
                phones: $app->make(PhoneNumberService::class),
                resolver: $app->make(WabaResolverService::class),
            )
        );

        $this->app->singleton(
            ComponentNormalizer::class
        );

        $this->app->singleton(
            Phone::class
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/waba.php' => config_path('waba.php'),
        ], 'waba-config');
    }
}
