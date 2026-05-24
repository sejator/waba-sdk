<?php

namespace Sejator\WabaSdk\Support\Embedded;

use Illuminate\Support\Str;
use Sejator\WabaSdk\Contracts\StateStore;
use Sejator\WabaSdk\Contracts\TokenStore;
use Sejator\WabaSdk\DTO\EmbeddedSignupResult;
use Sejator\WabaSdk\DTO\EmbeddedSignupSession;
use Sejator\WabaSdk\Exceptions\EmbeddedSignupException;
use Sejator\WabaSdk\Services\WabaResolverService;

class OAuthCallbackHandler
{
    public function __construct(
        protected TokenExchanger $tokenExchanger,
        protected StateStore $states,
        protected TokenStore $tokens,
        protected WabaResolverService $resolver,
    ) {}

    /**
     * Handle OAuth Callback
     *
     * Responsibilities:
     *
     * - validate state
     * - exchange token
     * - resolve WABA
     * - persist token
     * - persist session
     *
     * NOT:
     * - fetch templates
     * - fetch phones
     * - subscribe webhook
     *
     */
    public function handle(string $code, string $state): EmbeddedSignupResult
    {

        $session = $this->state($state);

        if (!$session) {
            throw new EmbeddedSignupException(
                'Invalid embedded signup state.'
            );
        }

        $token = $this->tokenExchanger->exchange($code);

        $accessToken = data_get(
            $token,
            'access_token'
        );

        if (!$accessToken) {

            throw new EmbeddedSignupException(
                'Meta access token not returned.'
            );
        }

        $wabaId = null;

        if (config('waba.embedded.auto_resolve_waba', true)) {
            $wabaId = $this->resolver
                ->resolveFromAccessToken(
                    $accessToken
                );
        }

        $this->tokens->put(
            $session->state,
            [
                'access_token' => $accessToken,
                'token_type' => data_get(
                    $token,
                    'token_type',
                    'bearer'
                ),
                'expires_in' => data_get(
                    $token,
                    'expires_in'
                ),
                'expires_at' => data_get(
                    $token,
                    'expires_in'
                ) ? now()->addSeconds(
                    data_get(
                        $token,
                        'expires_in'
                    )
                ) : null,
                'scopes' => [
                    'whatsapp_business_management',
                    'whatsapp_business_messaging',
                ],
                'metadata' => [
                    'waba_id' => $wabaId,
                    'embedded_version' => config(
                        'waba.embedded.version'
                    ),
                ],

            ]
        );

        $completed = $session->completed([
            'status' => 'completed',
            'code' => $code,
            'access_token' => $accessToken,
            'waba_id' => $wabaId,
            'completed_at' => now(),
            'metadata' => [
                'embedded_version' => config(
                    'waba.embedded.version'
                ),
                'api_version' => config(
                    'waba.meta.api_version'
                ),
            ],

        ]);

        $this->states->put(
            $completed
        );

        return EmbeddedSignupResult::fromArray([
            'status' => 'completed',
            'access_token' => $accessToken,
            'waba_id' => $wabaId,
            'metadata' => [
                'embedded_version' => config(
                    'waba.embedded.version'
                ),
                'api_version' => config(
                    'waba.meta.api_version'
                ),
            ],
        ]);
    }

    public function createSession(array $attributes = []): EmbeddedSignupSession
    {
        $session = EmbeddedSignupSession::make([
            'state' => Str::uuid()->toString(),
            'status' => 'pending',
            'created_at' => now(),
            ...$attributes,
        ]);

        $this->states->put(
            $session
        );

        return $session;
    }

    public function state(string $state): ?EmbeddedSignupSession
    {
        return $this->states
            ->get($state);
    }

    public function pull(string $state): ?EmbeddedSignupSession
    {
        return $this->states
            ->pull($state);
    }

    public function forget(string $state): void
    {
        $this->states
            ->forget($state);

        $this->tokens
            ->forget($state);
    }
}
