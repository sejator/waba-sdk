# Sejator WABA SDK (Laravel)

Laravel-native SDK for **WhatsApp Cloud API (WABA)**.

SDK ini dirancang khusus untuk Laravel dan mendukung:

- WhatsApp Cloud API
- Embedded Signup (OAuth & Embedded Flow)
- Messaging, Media, Template, Phone Number
- Webhook verification & parsing

---

## Requirements

- PHP >= 8.1
- Laravel 10.x / 11.x
- Meta (Facebook) Developer Account

---

## Installation

### Local Development (Path Repository)

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

---

## Service Provider & Facade

Package ini menggunakan Laravel auto-discovery.

Facade tersedia sebagai:

```php
use Waba;
```

Alias:

```php
Sejator\WabaSdk\Facades\Waba
```

---

## Configuration

Package ini sudah menyediakan file konfigurasi bawaan.

### Publish Config

```bash
php artisan vendor:publish \
  --provider="Sejator\WabaSdk\WabaServiceProvider" \
  --tag=waba-config
```

Setelah publish, file berikut akan tersedia:

```text
config/waba.php
```

---

## Environment Variables (.env)

```env
# Meta / Facebook App
META_APP_ID=
META_APP_SECRET=

# System User (Admin / BSP)
META_SYSTEM_USER_ID=
META_SYSTEM_USER_TOKEN=

# Graph API
META_GRAPH_URL=https://graph.facebook.com
META_GRAPH_VERSION=v24.0

# OAuth / Embedded Signup
META_OAUTH_BASE_URL=https://www.facebook.com
META_OAUTH_VERSION=v24.0
META_OAUTH_REDIRECT_URI=https://your-domain.com/waba/callback
META_EMBEDDED_CONFIG_ID=

# Webhook
META_WEBHOOK_URL=https://your-domain.com/api/waba
META_WEBHOOK_VERIFY_TOKEN=

# Application Mode
META_APP_MODE=production
```

---

## Basic Usage

### Set Access Token

```php
$waba = Waba::withAccessToken($accessToken);
```

---

## Messaging API

### Send Text Message

```php
Waba::withAccessToken($token)
    ->messages($phoneNumberId)
    ->sendText(
        to: '628xxxxxxxxx',
        text: 'Hello from Sejator WABA SDK'
    );
```

---

## Template API

```php
Waba::withAccessToken($token)
    ->templates($wabaId)
    ->list();
```

---

## Media API

### Upload Media

```php
Waba::withAccessToken($token)
    ->media()
    ->upload(
        filePath: storage_path('app/image.jpg'),
        mimeType: 'image/jpeg'
    );
```

---

## Embedded Signup

### Build Embedded Signup URL

```php
$url = Waba::embeddedSignup()
    ->withRedirectUri(route('waba.callback'))
    ->build();
```

### Exchange Embedded Code

```php
$response = Waba::exchangeEmbeddedCode($code);
```

---

## Webhook

### Verify Signature

```php
$isValid = Waba::verifyWebhookSignature(
    $request->getContent(),
    config('waba.meta.app_secret'),
    $request->header('X-Hub-Signature-256')
);
```

### Parse Payload

```php
$payload = Waba::parseWebhook($request->getContent());
```

---

## Error Handling

```php
try {
    // call SDK
} catch (Throwable $e) {
    report($e);
}
```
