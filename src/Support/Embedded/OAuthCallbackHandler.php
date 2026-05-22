<?php

namespace Sejator\WabaSdk\Support\Embedded;

use Illuminate\Support\Str;
use Sejator\WabaSdk\DTO\EmbeddedSignupResult;
use Sejator\WabaSdk\DTO\EmbeddedSignupSession;
use Sejator\WabaSdk\Exceptions\EmbeddedSignupException;

class OAuthCallbackHandler
{
    public function __construct(
        protected TokenExchanger $tokenExchanger,
        protected EmbeddedStateManager $stateManager,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Handle OAuth Callback
    |--------------------------------------------------------------------------
    |
    | Responsibilities:
    |
    | - validate state
    | - exchange token
    | - persist session
    |
    | NOT:
    | - fetch WABA
    | - fetch phones
    | - subscribe webhook
    |
    */

    public function handle(
        string $code,
        string $state
    ): EmbeddedSignupResult {

        /*
        |--------------------------------------------------------------------------
        | Validate Session
        |--------------------------------------------------------------------------
        */

        $session = $this->state(
            $state
        );

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
        | Complete Session
        |--------------------------------------------------------------------------
        */

        $completed = $session->completed([

            'code' => $code,

            'access_token' =>
            $accessToken,

            'status' => 'completed',

            /*
            |--------------------------------------------------------------------------
            | Raw Payload
            |--------------------------------------------------------------------------
            */

            'payload' => [

                'token' => $token,

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

            'access_token' =>
            $accessToken,

            'status' => 'completed',

            'payload' => [

                'token' => $token,

            ],

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create Session
    |--------------------------------------------------------------------------
    */

    public function createSession(
        array $attributes = []
    ): EmbeddedSignupSession {

        $session = EmbeddedSignupSession::make([

            'state' =>
            Str::uuid()->toString(),

            ...$attributes,

        ]);

        $this->stateManager->put(
            $session
        );

        return $session;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Session
    |--------------------------------------------------------------------------
    */

    public function state(
        string $state
    ): ?EmbeddedSignupSession {

        return $this->stateManager->get(
            $state
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Forget Session
    |--------------------------------------------------------------------------
    */

    public function forget(
        string $state
    ): void {

        $this->stateManager->forget(
            $state
        );
    }
}
