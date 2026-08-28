<?php

namespace Sejator\WabaSdk\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Sejator\WabaSdk\Exceptions\GraphApiException;

class Client
{
    protected ?int $retryTimes = null;
    protected ?int $retrySleepMs = null;
    protected ?int $requestTimeout = null;

    public function __construct(
        protected string $baseUrl,
        protected ?string $token = null,
    ) {}

    /**
     * Override retry/timeout HANYA untuk instance ini (clone, bukan ubah
     * config global) - dipakai konsumen yang punya deadline ketat (mis.
     * Calls API, batas 30-60 detik dari Meta) tanpa mempengaruhi default
     * config('waba.http.retry.*') yang dipakai service lain.
     */
    public function withRetry(int $times, int $sleepMs = 500): static
    {
        $clone = clone $this;
        $clone->retryTimes = $times;
        $clone->retrySleepMs = $sleepMs;

        return $clone;
    }

    public function withTimeout(int $seconds): static
    {
        $clone = clone $this;
        $clone->requestTimeout = $seconds;

        return $clone;
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP Methods
    |--------------------------------------------------------------------------
    */

    public function get(string $uri, array $query = []): array
    {
        return $this->handle(
            $this->http()->get(
                $this->url($uri),
                $query,
            ),
        );
    }

    public function post(string $uri, array $payload = []): array
    {
        return $this->handle(
            $this->http()->post(
                $this->url($uri),
                $payload,
            ),
        );
    }

    public function put(string $uri, array $payload = []): array
    {
        return $this->handle(
            $this->http()->put(
                $this->url($uri),
                $payload,
            ),
        );
    }

    public function patch(string $uri, array $payload = []): array
    {
        return $this->handle(
            $this->http()->patch(
                $this->url($uri),
                $payload,
            ),
        );
    }

    public function delete(string $uri, array $payload = []): array
    {
        return $this->handle(
            $this->http()->delete(
                $this->url($uri),
                $payload,
            ),
        );
    }

    public function postWithQuery(string $uri, array $query = []): array
    {
        return $this->handle(
            $this->http()
                ->withOptions([
                    'query' => $query,
                ])
                ->post(
                    $this->url($uri),
                ),
        );
    }

    public function multipart(string $uri, array $multipart): array
    {
        $http = $this->http();

        foreach ($multipart as $part) {

            $http = $http->attach(
                $part['name'],
                $part['contents'],
                $part['filename'] ?? null,
                $part['headers'] ?? [],
            );
        }

        return $this->handle(
            $http->post(
                $this->url($uri),
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Binary Upload
    |--------------------------------------------------------------------------
    */

    public function uploadBinary(string $uploadId, string $binary, int $offset = 0): array
    {
        return $this->handle(
            $this->http()
                ->withHeaders([
                    'file_offset' => $offset,
                    'Content-Type' => 'application/octet-stream',
                ])
                ->send(
                    'POST',
                    $this->url($uploadId),
                    [
                        'body' => $binary,
                    ],
                ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Raw Download
    |--------------------------------------------------------------------------
    */

    public function getRaw(string $url): string
    {
        $response = $this->http()->get($url);

        if ($response->successful()) {
            return $response->body();
        }

        $this->logFailedResponse($response);

        throw GraphApiException::fromResponse(
            $response->json(),
            $response->status(),
        );
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function withToken(?string $token): static
    {
        return new static(
            $this->baseUrl,
            $token,
        );
    }

    public function system(): static
    {
        return $this->withToken(
            config('waba.meta.system_user_token'),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    */

    protected function http(): PendingRequest
    {
        $http = Http::acceptJson()
            ->withHeaders([
                'User-Agent' => 'Sejator-WabaSDK/1.0',
                'X-Request-ID' => (string) str()->uuid(),
            ])
            ->timeout(
                $this->requestTimeout ?? config('waba.http.timeout', 30),
            )
            ->connectTimeout(
                config('waba.http.connect_timeout', 10),
            )
            ->retry(
                $this->retryTimes ?? config('waba.http.retry.times', 2),
                $this->retrySleepMs ?? config('waba.http.retry.sleep', 500),

                function ($exception) {

                    if (! $exception instanceof RequestException) {
                        return true;
                    }

                    if (! $exception->response) {
                        return true;
                    }

                    return GraphApiException::fromResponse(
                        $exception->response->json(),
                        $exception->response->status(),
                    )->isRetryable();
                },

                throw: false,
            );

        if ($this->token) {
            $http = $http->withToken(
                $this->token,
            );
        }

        return $http;
    }

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    protected function handle(Response $response): array
    {

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $this->logFailedResponse(
            $response,
        );

        throw GraphApiException::fromResponse(
            $response->json(),
            $response->status(),
        );
    }

    protected function logFailedResponse(Response $response): void
    {
        $request = $response->transferStats?->getRequest();

        logger()->error(
            'Meta Graph API Error',
            [
                'method' => $request?->getMethod(),
                'url' => (string) $request?->getUri(),
                'status' => $response->status(),
                'request_id' => $response->header(
                    'x-fb-request-id',
                ),
                'trace_id' => data_get(
                    $response->json(),
                    'error.fbtrace_id',
                ),
                'response' => $response->json(),
                'raw' => $response->body(),
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function url(string $uri): string
    {
        return rtrim(
            $this->baseUrl,
            '/',
        ) . '/' . ltrim(
            $uri,
            '/',
        );
    }
}
