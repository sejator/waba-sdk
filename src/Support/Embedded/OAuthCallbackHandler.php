<?php

namespace Sejator\WabaSdk\Support\Embedded;

use Illuminate\Support\Str;
use Sejator\WabaSdk\DTO\EmbeddedSignupResult;
use Sejator\WabaSdk\DTO\EmbeddedSignupSession;
use Sejator\WabaSdk\Exceptions\EmbeddedSignupException;
use Sejator\WabaSdk\Services\Client;

class OAuthCallbackHandler
{
    public function __construct(
        protected Client $client,
        protected TokenExchanger $tokenExchanger,
        protected EmbeddedStateManager $stateManager,
    ) {}

    public function handle(string $code, string $state): EmbeddedSignupResult
    {

        /*
        |--------------------------------------------------------------------------
        | Validate Session State
        |--------------------------------------------------------------------------
        */

        $session = $this->state($state);

        if (!$session) {

            throw new EmbeddedSignupException(
                'Invalid embedded signup state.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Exchange Access Token
        |--------------------------------------------------------------------------
        */

        $token = $this->tokenExchanger
            ->exchange($code);

        $accessToken = data_get(
            $token,
            'access_token'
        );

        if (!$accessToken) {

            throw new EmbeddedSignupException(
                'Failed to retrieve access token.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Graph Client
        |--------------------------------------------------------------------------
        */

        $graph = $this->client
            ->withToken($accessToken);

        /*
        |--------------------------------------------------------------------------
        | Business Account
        |--------------------------------------------------------------------------
        */

        $me = $graph->get('me', [
            'fields' => 'id,name',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Complete Session
        |--------------------------------------------------------------------------
        */

        $completed = $session->completed([
            'code' => $code,
            'access_token' => $accessToken,
            'business_id' => data_get(
                $me,
                'id'
            ),
            'business_name' => data_get(
                $me,
                'name'
            ),
            'status' => 'completed',
            /*
            |--------------------------------------------------------------------------
            | Original Token Payload
            |--------------------------------------------------------------------------
            */

            'payload' => [
                'token' => $token,
                'me' => $me,
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Persist Session
        |--------------------------------------------------------------------------
        */

        $this->stateManager->put(
            $completed
        );

        /*
        |--------------------------------------------------------------------------
        | Result DTO
        |--------------------------------------------------------------------------
        */

        return EmbeddedSignupResult::fromArray([
            'access_token' => $accessToken,
            'business_id' => data_get(
                $me,
                'id'
            ),
            'business_name' => data_get(
                $me,
                'name'
            ),
            'status' => 'completed',
            'payload' => [
                'token' => $token,
                'me' => $me,
            ],

        ]);
    }

    public function createSession(array $attributes = []): EmbeddedSignupSession
    {
        $session = EmbeddedSignupSession::make([
            'state' => Str::uuid()->toString(),
            ...$attributes,
        ]);

        $this->stateManager->put(
            $session
        );

        return $session;
    }

    public function state(string $state): ?EmbeddedSignupSession
    {
        return $this->stateManager->get(
            $state
        );
    }

    public function forget(string $state): void
    {

        $this->stateManager->forget(
            $state
        );
    }
}
