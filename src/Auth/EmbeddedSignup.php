<?php

namespace Sejator\WabaSdk\Auth;

use RuntimeException;

class EmbeddedSignup
{
    protected string $clientId;
    protected string $redirectUri;
    protected string $state;
    protected string $responseType = 'code';
    protected ?string $scope = null;

    protected string $baseUrl;

    public function __construct()
    {
        $base = rtrim(config('waba.meta.oauth.base_url'), '/');
        $ver  = config('waba.meta.oauth.version');

        if (!$base || !$ver) {
            throw new RuntimeException('OAuth config tidak lengkap');
        }

        $this->baseUrl = "{$base}/{$ver}/dialog/oauth";
    }

    public static function make(): self
    {
        return new self();
    }

    public function clientId(string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function redirectUri(string $redirectUri): self
    {
        if (!str_starts_with($redirectUri, 'https://')) {
            throw new RuntimeException('redirect_uri harus HTTPS');
        }

        $this->redirectUri = $redirectUri;
        return $this;
    }

    public function state(string $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function scope(string $scope): self
    {
        $this->scope = $scope;
        return $this;
    }

    public function build(): string
    {
        foreach (['clientId', 'redirectUri', 'state'] as $prop) {
            if (!isset($this->{$prop})) {
                throw new RuntimeException("{$prop} belum di-set");
            }
        }

        return $this->baseUrl . '?' . http_build_query([
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'state'         => $this->state,
            'response_type' => $this->responseType,
            'scope'         => $this->scope,
            'display'       => 'popup',
            'extras'        => json_encode([
                'setup' => [
                    'channel' => 'WHATSAPP'
                ]
            ]),
        ]);
    }

    public function __toString(): string
    {
        return $this->build();
    }
}
