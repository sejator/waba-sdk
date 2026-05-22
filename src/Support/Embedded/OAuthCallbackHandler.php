<?php

namespace Sejator\WabaSdk\Support\Embedded;

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

    public function handle(
        string $code,
        string $state
    ): EmbeddedSignupResult {

        /*
        |--------------------------------------------------------------------------
        | Validate State
        |--------------------------------------------------------------------------
        */

        $session = $this->stateManager->get(
            $state
        );

        if (!$session) {

            throw new EmbeddedSignupException(
                'Invalid embedded signup state.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Exchange Token
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
        | Fetch Business Account
        |--------------------------------------------------------------------------
        */

        $graph = $this->client
            ->withToken($accessToken);

        $me = $graph->get('me', [
            'fields' => 'id,name',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Complete Session
        |--------------------------------------------------------------------------
        */

        $completed = $session->completed([

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

        ]);

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

        ]);
    }

    public function createSession(): EmbeddedSignupSession
    {
        $session = EmbeddedSignupSession::make(
            str()->uuid()->toString()
        );

        $this->stateManager->put(
            $session
        );

        return $session;
    }
}
