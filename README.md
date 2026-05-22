# Sejator WABA SDK (Laravel)

Laravel-native SDK untuk membangun integrasi **WhatsApp Cloud API (WABA)** secara modern, scalable, dan production-ready.

SDK ini dirancang untuk:

- SaaS Omnichannel
- WhatsApp BSP Platform
- CRM & Customer Support
- Multi-device WhatsApp Management
- Embedded Signup Flow
- Multi-tenant Architecture

---

## Features

### WhatsApp Cloud API

- Send text message
- Send image, video, document, audio
- Interactive button & list
- Template message
- Mark as read
- Media upload & download

### Embedded Signup

- Official Meta Embedded Signup
- OAuth Popup Flow
- Token Exchange
- Embedded Session State Manager
- Webhook Subscription

### Webhook System

- Signature verification
- Webhook parser
- Incoming message normalizer
- Message type resolver

### Business Management

- Business account information
- WABA management
- Phone number management

### Production Ready

- Retry support
- Timeout handling
- Structured exceptions
- DTO-based architecture
- Enum support
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
php artisan vendor:publish \
  --provider="Sejator\WabaSdk\WabaServiceProvider" \
  --tag=waba-config
```

File config:

```txt
config/waba.php
```

---

# Environment Variables

```env
/*
|--------------------------------------------------------------------------
| Meta App
|--------------------------------------------------------------------------
*/

META_APP_ID=
META_APP_SECRET=

/*
|--------------------------------------------------------------------------
| Graph API
|--------------------------------------------------------------------------
*/

META_BASE_URL=https://graph.facebook.com/v23.0

/*
|--------------------------------------------------------------------------
| Default Access Token
|--------------------------------------------------------------------------
|
| Optional.
|
*/

META_ACCESS_TOKEN=

/*
|--------------------------------------------------------------------------
| Embedded Signup
|--------------------------------------------------------------------------
*/

META_CONFIGURATION_ID=

META_REDIRECT_URI=https://your-domain.com/embedded/callback

META_OAUTH_VERSION=v23.0

/*
|--------------------------------------------------------------------------
| Webhook
|--------------------------------------------------------------------------
*/

META_VERIFY_TOKEN=
```

---

# Package Structure

```txt
src
├── Contracts
├── DTO
├── Enums
├── Exceptions
├── Facades
├── Services
├── Support
│   ├── Embedded
│   └── Webhooks
├── Traits
├── Utils
└── WabaServiceProvider
```

---

# Basic Usage

## Import Facade

```php
use Waba;
```

atau:

```php
use Sejator\WabaSdk\Facades\Waba;
```

---

# Messaging API

## Send Text Message

```php
Waba::messages()
    ->text(
        to: '628xxxx',
        body: 'Hello World'
    );
```

---

## Send Image

```php
Waba::messages()
    ->image(
        to: '628xxxx',
        image: 'https://example.com/image.jpg',
        caption: 'Image Caption'
    );
```

---

## Send Template

```php
Waba::messages()
    ->template(
        to: '628xxxx',
        name: 'hello_world',
        language: 'id'
    );
```

---

# Media API

## Upload Media

```php
Waba::media()
    ->upload(
        path: storage_path('app/image.jpg'),
        mime: 'image/jpeg'
    );
```

---

## Download Media

```php
Waba::media()
    ->download($mediaId);
```

---

# Template API

## Get Templates

```php
Waba::templates($wabaId)
    ->all();
```

---

## Create Template

```php
Waba::templates($wabaId)
    ->create([
        'name' => 'promo_template',
        'language' => 'id',
        'category' => 'MARKETING',
        'components' => [
            ...
        ]
    ]);
```

---

# Business API

## Get Business Profile

```php
Waba::business()
    ->me();
```

---

# Phone Number API

## Get Phone Numbers

```php
Waba::phoneNumbers()
    ->all($wabaId);
```

---

# Embedded Signup

SDK mendukung official Meta Embedded Signup popup flow.

---

## Generate Embedded Signup URL

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

        'redirect' => route('devices.index'),

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

## Open Popup

```js
window.open(url, "metaEmbeddedSignup", "width=560,height=820");
```

---

# OAuth Callback

## Route

```php
Route::get(
    '/embedded/callback',
    EmbeddedSignupCallbackController::class
)->name('embedded.callback');
```

---

## Controller Example

```php
$session = $handler->state(
    request('state')
);

$tenantId = data_get(
    $session->context,
    'tenant_id'
);

$userId = data_get(
    $session->context,
    'user_id'
);

$result = $handler->handle(
    code: request('code'),
    state: request('state')
);
```

---

# Webhook Verification

## Verify Webhook

```php
use Sejator\WabaSdk\Support\Webhooks\WebhookVerifier;

$verifier = app(
    WebhookVerifier::class
);

$challenge = $verifier->verify(
    request('hub_mode'),
    request('hub_verify_token'),
    request('hub_challenge'),
);
```

---

# Webhook Signature Validation

```php
use Sejator\WabaSdk\Support\Webhooks\WebhookSignatureValidator;

$validator = app(
    WebhookSignatureValidator::class
);

$isValid = $validator->validate(
    request()
);
```

---

# Parse Incoming Messages

```php
use Sejator\WabaSdk\Support\Webhooks\WebhookParser;

$parser = app(
    WebhookParser::class
);

$messages = $parser->parse(
    request()->all()
);
```

---

# Error Handling

```php
use Sejator\WabaSdk\Exceptions\WabaException;

try {

    // SDK call

} catch (WabaException $e) {

    report($e);

}
```

---

# Architecture

```txt
Controller
│
├── Services
│   ├── MessageService
│   ├── TemplateService
│   ├── MediaService
│   ├── EmbeddedSignupService
│   ├── BusinessService
│   └── PhoneNumberService
│
├── Support
│   ├── Embedded
│   ├── Webhooks
│   └── Normalizers
│
├── DTO
│
└── Client
    └── Meta Graph API
```

---

# Production Notes

## Embedded Signup Callback

Disarankan callback route:

- tidak memakai middleware auth,
- tidak bergantung session Laravel,
- menggunakan OAuth state context.

---

## Multi Tenant

SDK mendukung multi-tenant architecture melalui:

```php
context => []
```

pada `EmbeddedSignupSession`.
