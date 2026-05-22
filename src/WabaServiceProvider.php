<?php

namespace Sejator\WabaSdk;

use Illuminate\Support\ServiceProvider;
use Sejator\WabaSdk\Services\BusinessService;
use Sejator\WabaSdk\Services\Client;
use Sejator\WabaSdk\Services\EmbeddedSignupService;
use Sejator\WabaSdk\Services\TemplateService;
use Sejator\WabaSdk\Services\MessageService;
use Sejator\WabaSdk\Services\PhoneNumberService;
use Sejator\WabaSdk\Services\WebhookService;
use Sejator\WabaSdk\Support\ComponentNormalizer;
use Sejator\WabaSdk\Support\Embedded\OAuthUrlGenerator;
use Sejator\WabaSdk\Support\Embedded\TokenExchanger;
use Sejator\WabaSdk\Support\Embedded\WebhookSubscriber;
use Sejator\WabaSdk\Support\IncomingMessageNormalizer;
use Sejator\WabaSdk\Support\MessagePreviewGenerator;
use Sejator\WabaSdk\Support\MessageTypeResolver;
use Sejator\WabaSdk\Support\Webhooks\WebhookVerifier;
use Sejator\WabaSdk\Utils\Phone;

class WabaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/waba.php', 'waba');

        $this->app->singleton(Client::class, fn() => new Client(
            config('waba.meta.base_url'),
            config('waba.meta.access_token')
        ));

        $this->app->singleton(ComponentNormalizer::class);

        $this->app->singleton(
            TemplateService::class,
            fn($app) =>
            new TemplateService(
                $app->make(Client::class),
                $app->make(ComponentNormalizer::class)
            )
        );

        $this->app->singleton(
            MessageService::class,
            fn($app) =>
            new MessageService($app->make(Client::class))
        );

        $this->app->singleton(
            MessageTypeResolver::class
        );

        $this->app->singleton(
            IncomingMessageNormalizer::class
        );

        $this->app->singleton(
            MessagePreviewGenerator::class
        );

        $this->app->singleton(
            Phone::class
        );

        /*
        |--------------------------------------------------------------------------
        | Embedded Signup
        |--------------------------------------------------------------------------
        */

        $this->app->singleton(
            OAuthUrlGenerator::class
        );

        $this->app->singleton(
            TokenExchanger::class
        );

        $this->app->singleton(
            WebhookSubscriber::class,
            fn($app) =>
            new WebhookSubscriber(
                $app->make(Client::class)
            )
        );

        $this->app->singleton(
            EmbeddedSignupService::class,
            fn($app) =>
            new EmbeddedSignupService(
                $app->make(Client::class),
                $app->make(OAuthUrlGenerator::class),
                $app->make(TokenExchanger::class),
                $app->make(WebhookSubscriber::class),
            )
        );

        $this->app->singleton(
            BusinessService::class,
            fn($app) =>
            new BusinessService(
                $app->make(Client::class)
            )
        );

        $this->app->singleton(
            PhoneNumberService::class,
            fn($app) =>
            new PhoneNumberService(
                $app->make(Client::class)
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Webhook
        |--------------------------------------------------------------------------
        */
        $this->app->singleton(
            WebhookVerifier::class
        );

        $this->app->singleton(
            WebhookService::class,
            fn($app) =>
            new WebhookService(
                $app->make(WebhookVerifier::class)
            )
        );
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/waba.php' => config_path('waba.php'),
        ], 'waba-config');
    }
}
