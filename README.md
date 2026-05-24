# WABA SDK (Laravel)

Laravel-native SDK untuk integrasi modern dengan WhatsApp Cloud API (WABA), Embedded Signup, dan arsitektur BSP multi-tenant.

SDK ini dirancang untuk:

- SaaS Omnichannel
- WhatsApp BSP Platform
- CRM & Customer Support
- Multi-device WhatsApp Management
- Embedded Signup Flow
- Multi-tenant Architecture

---

# Features

## WhatsApp Cloud API

- Send text message
- Send image, video, audio, document
- Interactive button & list
- Template message
- Mark message as read
- Media upload & download
- Multi-device messaging

---

## Embedded Signup

- Official Meta Embedded Signup
- OAuth Popup Flow
- Runtime Token Exchange
- Embedded Session State Manager
- Webhook Subscription
- Multi-tenant signup context

---

## Business Management

- Shared WABA support
- WABA information
- Phone number management
- Phone registration & verification
- Business account management

---

## Webhook System

- Webhook signature verification
- Incoming payload parser
- Message normalizer
- Message type resolver

---

## Production Ready

- Runtime token architecture
- DTO-based architecture
- Enum support
- Structured exceptions
- Retry support
- Timeout handling
- Immutable client architecture
- Queue-safe services
- Multi-tenant ready

---

# Requirements

- PHP >= 8.2
- Laravel >= 12
- Meta Developer Account
- WhatsApp Business Platform Access

---

# Installation

## Composer

```bash
composer require sejator/waba-sdk
```

---

# Publish Configuration

```bash
php artisan vendor:publish   --provider="Sejator\WabaSdk\WabaServiceProvider" --tag=waba-config
```

---

# Environment Variables

```env
# Meta App

META_APP_ID=
META_APP_SECRET=

# Graph API

META_BASE_URL=https://graph.facebook.com/v25.0

# Runtime System User Token

META_SYSTEM_USER_TOKEN=

# Embedded Signup

META_CONFIGURATION_ID=
META_REDIRECT_URI=https://your-domain.com/embedded/callback
META_OAUTH_VERSION=v25.0

# Webhook

META_VERIFY_TOKEN=
```

---

# Configuration

## config/waba.php

```php
<?php

return [

    'meta' => [

        'app_id' => env(
            'META_APP_ID'
        ),

        'app_secret' => env(
            'META_APP_SECRET'
        ),

        'api_version' => env(
            'META_API_VERSION',
            'v25.0'
        ),

        'base_url' => sprintf(
            'https://graph.facebook.com/%s',
            env('META_API_VERSION', 'v25.0')
        ),

        'access_token' => env(
            'META_ACCESS_TOKEN'
        ),

        'system_user_token' => env(
            'META_SYSTEM_USER_TOKEN'
        ),

        'business_id' => env(
            'META_BUSINESS_ID'
        ),

    ],

    'embedded' => [

        'config_id' => env(
            'META_CONFIGURATION_ID'
        ),

        'redirect_uri' => env(
            'META_REDIRECT_URI'
        ),

        'oauth_version' => env(
            'META_OAUTH_VERSION',
            env('META_API_VERSION', 'v25.0')
        ),

        'version' => env(
            'META_EMBEDDED_VERSION',
            'v4'
        ),

        'auto_resolve_waba' => env(
            'META_AUTO_RESOLVE_WABA',
            true
        ),

        'auto_subscribe_webhook' => env(
            'META_AUTO_SUBSCRIBE_WEBHOOK',
            true
        ),

        'auto_fetch_phone_numbers' => env(
            'META_AUTO_FETCH_PHONES',
            true
        ),

        'auto_fetch_templates' => env(
            'META_AUTO_FETCH_TEMPLATES',
            true
        ),

        'state_ttl' => env(
            'META_STATE_TTL',
            300
        ),
    ],

    'http' => [
        'timeout' => env(
            'WABA_HTTP_TIMEOUT',
            30
        ),

        'retry' => [
            'times' => env(
                'WABA_HTTP_RETRY_TIMES',
                2
            ),

            'sleep' => env(
                'WABA_HTTP_RETRY_SLEEP',
                500
            ),
        ],
    ],
];

```

---

# Package Structure

```txt
src
├── Contracts
├── DTO
├── Enums
├── Exceptions
├── Services
├── Support
│   ├── Auth
│   └── Embedded
├── Utils
└── WabaServiceProvider
```

---

# Runtime Client Architecture

SDK menggunakan runtime token architecture.

Contoh:

```php
use Sejator\WabaSdk\Services\Client;
use Sejator\WabaSdk\Services\MessageService;

$client = app(Client::class)
    ->withToken($device->access_token);

$messageService = new MessageService(
    $client
);
```

---

# Send Text Message

```php
$response = $messageService->text(
    phoneNumberId: $device->phone_number_id,
    to: '628xxxx',
    text: 'Hello World'
);
```

---

# Send Media Message

## Image

```php
$response = $messageService->media(
    phoneNumberId: $device->phone_number_id,
    to: '628xxxx',
    type: 'image',
    url: 'https://example.com/image.jpg',
    caption: 'Image Caption'
);
```

---

## Document

```php
$response = $messageService->media(
    phoneNumberId: $device->phone_number_id,
    to: '628xxxx',
    type: 'document',
    url: 'https://example.com/file.pdf',
    caption: 'Invoice',
    filename: 'invoice.pdf'
);
```

---

# Send Template Message

```php
$response = $messageService->template(
    $device->phone_number_id,
    [
        'to' => '628xxxx',
        'template' => [
            'name' => 'hello_world',
            'language' => [
                'code' => 'id'
            ],
        ],
    ]
);
```

---

# Media Service

## Upload Media

```php
use Sejator\WabaSdk\Services\MediaService;

$media = new MediaService($client);

$response = $media->upload(
    $device->phone_number_id,
    storage_path('app/image.jpg')
);
```

---

## Download Media

```php
$binary = $media->download($mediaUrl);
```

---

# Template Service

## Get Templates

```php
use Sejator\WabaSdk\Services\TemplateService;

$templates = $service->all(
    $device->waba_id
);
```

---

## Create Template

```php
$response = $service->create(
    $device->waba_id,
    [
        'name' => 'promo_template',
        'language' => 'id',
        'category' => 'MARKETING',
        'components' => [
            [
                'type' => 'BODY',
                'text' => 'Halo {{1}}'
            ]
        ],
    ]
);
```

---

# Embedded Signup

## Generate Signup URL

```php
use Sejator\WabaSdk\Services\EmbeddedSignupService;
use Sejator\WabaSdk\Support\Embedded\OAuthCallbackHandler;

$handler = app(
    OAuthCallbackHandler::class
);

$session = $handler->createSession([
    'context' => [
        'tenant_id' => tenant()->id,
        'user_id' => auth()->id(),
    ],
]);

$embedded = app(
    EmbeddedSignupService::class
);

$signup = $embedded->signupUrl(
    $session->state
);

$url = $signup['url'];
```

---

# Open Embedded Signup Popup

```js
window.open(url, "metaEmbeddedSignup", "width=560,height=820");
```

---

# OAuth Callback Example

```php
$result = $handler->handle(
    code: request('code'),
    state: request('state')
);

$accessToken = $result->accessToken;
```

---

# Error Handling

```php
use Sejator\WabaSdk\Exceptions\GraphApiException;

try {

    // SDK Call

} catch (GraphApiException $e) {

    logger()->error(
        $e->toArray()
    );

}
```

---

# Multi Tenant Example

```php
$client = app(Client::class)
    ->withToken(
        $device->access_token
    );

$messageService = new MessageService(
    $client
);
```

---

# Production Notes

## Embedded Signup Callback

Disarankan callback route:

- tidak memakai auth middleware
- tidak bergantung session Laravel
- menggunakan OAuth state context
- menggunakan HTTPS

---

## Media URL

WhatsApp Cloud API mewajibkan:

- HTTPS public URL
- publicly accessible media
- valid mime type

---

# Author

[Sejator Dev](https://github.com/sejator)
