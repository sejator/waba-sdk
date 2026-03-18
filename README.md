# Sejator WABA SDK (Laravel)

Laravel-native SDK untuk **WhatsApp Cloud API (WABA)**.

SDK ini dirancang untuk kebutuhan **production & SaaS (Omnichannel / BSP)** dengan fitur lengkap:

- WhatsApp Cloud API (Messaging, Media, Template)
- Embedded Signup (OAuth & Embedded Flow)
- System User & BSP Management
- Credit Line (Billing)
- Webhook Verification & Parsing
- Multi-tenant ready
- Retry, timeout & logging (production-grade)

---

## Requirements

- PHP >= 8.2
- Laravel 12.x
- Meta (Facebook) Developer Account

---

## Installation

### Local Development (Path Repository)

1. Tambahkan repository path di composer.json

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../waba-sdk"
    }
  ],
  "require": {
    "sejator/waba-sdk": "*"
  }
}
```

```bash
composer update sejator/waba-sdk
```

2. Install package

```bash
composer require sejator/waba-sdk:*
```

## Service Provider & Facade

Auto-discovery aktif secara default.

Facade:

```php
use Waba;
```

Alias:

```php
Sejator\WabaSdk\Facades\Waba
```

## Configuration

Publish Config

```bash
php artisan vendor:publish \
  --provider="Sejator\WabaSdk\WabaServiceProvider" \
  --tag=waba-config
```

File config:

```
config/waba.php
```

## Environment Variables (.env)

```env
# Meta App
META_APP_ID=
META_APP_SECRET=

# Graph API
META_GRAPH_URL=https://graph.facebook.com
META_GRAPH_VERSION=v24.0

# OAuth / Embedded Signup
META_OAUTH_BASE_URL=https://www.facebook.com
META_OAUTH_VERSION=v24.0
META_OAUTH_REDIRECT_URI=https://your-domain.com/waba/callback

# Webhook
META_WEBHOOK_VERIFY_TOKEN=

# Default Token (optional)
WABA_TOKEN=

# HTTP Config
WABA_HTTP_TIMEOUT=10
WABA_HTTP_RETRY=3
```

## Basic Usage

Set Access Token (Multi-tenant)

```php
$waba = Waba::withAccessToken($token);
```

## Messaging API

- Send Text

```php
Waba::withAccessToken($token)
    ->messages($phoneNumberId)
    ->text('628xxxx', 'Hello world');
```

- Send Image

```php
->image($to, $url, $caption);
```

- Send Template

```php
->template($to, 'hello_world', 'id', $components);
```

- Mark as Read

```php
->markAsRead($messageId);
```

## Media API

- Upload

```php
Waba::withAccessToken($token)
    ->media()
    ->upload(storage_path('app/image.jpg'), 'image/jpeg');
```

## Template API

```php
Waba::withAccessToken($token)
    ->templates($wabaId)
    ->list();
```

## Billing (Credit Line)

```php
Waba::withAccessToken($token)
    ->credit()
    ->listCreditLines($businessId);
```

## Embedded Signup

- Generate URL

```php
$url = Waba::embeddedSignup()
    ->clientId()
    ->redirectUri(route('waba.callback'))
    ->state(csrf_token())
    ->build();
```

- Exchange Code

```php
$data = Waba::exchangeEmbeddedCode($code);
```

## BSP / Admin Flow

- Create System User

```php
$userId = Waba::admin()
    ->createSystemUser($businessId);
```

- Assign WABA

```php
Waba::admin()->assignWabaAsset($userId, $wabaId);
```

- Generate Token

```php
$token = Waba::admin()->generateToken($userId);
```

## Embedded User Flow

```php
Waba::withAccessToken($token)
    ->embedded()
    ->subscribeAppToWaba($wabaId);
```

## Webhook

- Verify Signature

```php
Waba::verifyWebhookSignature(
    $request->getContent(),
    config('waba.meta.app_secret'),
    $request->header('X-Hub-Signature-256')
);
```

- Parse Payload

```php
$payload = Waba::parseWebhook($request->getContent());

$type = $payload->type(); // message | status | template
```

## Error Handling

```php
try {
    // call SDK
} catch (\Sejator\WabaSdk\Exceptions\WabaException $e) {
    report($e);
}
```
