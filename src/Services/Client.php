<?php

namespace Sejator\WabaSdk\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Sejator\WabaSdk\Exceptions\GraphApiException;

class Client
{
    public function __construct(
        protected string $baseUrl,
        protected ?string $token = null,
    ) {}

    public function get(string $uri, array $query = []): array
    {
        return $this->handle(
            $this->http()->get(
                $this->url($uri),
                $query
            )
        );
    }

    public function post(string $uri, array $payload = []): array
    {
        return $this->handle(
            $this->http()->post(
                $this->url($uri),
                $payload
            )
        );
    }

    public function delete(string $uri, array $payload = []): array
    {
        return $this->handle(
            $this->http()->delete(
                $this->url($uri),
                $payload
            )
        );
    }

    public function patch(string $uri, array $payload = []): array
    {
        return $this->handle(
            $this->http()->patch(
                $this->url($uri),
                $payload
            )
        );
    }

    public function put(string $uri, array $payload = []): array
    {
        return $this->handle(
            $this->http()->put(
                $this->url($uri),
                $payload
            )
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
                    $this->url($uri)
                )
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
                $part['headers'] ?? []
            );
        }

        return $this->handle(
            $http->post(
                $this->url($uri)
            )
        );
    }

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
                    ]
                )
        );
    }

    public function getRaw(string $url): string
    {
        $response = $this->http()
            ->get($url);

        if ($response->successful()) {

            return $response->body();
        }

        throw GraphApiException::fromResponse(
            $response->json(),
            $response->status()
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
            $token
        );
    }

    public function system(): static
    {
        return $this->withToken(
            config('waba.meta.system_user_token')
        );
    }

    protected function http(): PendingRequest
    {
        $http = Http::acceptJson()
            ->withHeaders([
                'User-Agent' => 'Sejator-WabaSDK/1.0',
                'X-Request-ID' => (string) str()->uuid(),
            ])
            ->timeout(
                config('waba.http.timeout', 30)
            )
            ->connectTimeout(
                config('waba.http.connect_timeout', 10)
            )
            ->retry(
                config('waba.http.retry.times', 2),
                config('waba.http.retry.sleep', 500),

                function ($exception) {

                    /**
                     * Connection timeout / DNS / socket error
                     */

                    if (!$exception instanceof RequestException) {
                        return true;
                    }

                    $response = $exception->response;

                    if (!$response) {
                        return true;
                    }

                    $graphException = GraphApiException::fromResponse(
                        $response->json(),
                        $response->status()
                    );

                    return $graphException->isRetryable();
                },

                throw: false
            );

        if ($this->token) {
            $http = $http->withToken(
                $this->token
            );
        }

        return $http;
    }

    protected function url(string $uri): string
    {
        return rtrim(
            $this->baseUrl,
            '/'
        ) . '/' . ltrim(
            $uri,
            '/'
        );
    }

    protected function handle(Response $response): array
    {

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $json = $response->json();

        throw GraphApiException::fromResponse(
            $json,
            $response->status()
        );
    }
}
