<?php

namespace Sejator\WabaSdk\Http;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Sejator\WabaSdk\Exceptions\WabaException;

class WabaClient
{
    protected string $baseUrl;
    protected string $version;
    protected string $token;

    protected HttpFactory $http;

    public function __construct(string $accessToken, HttpFactory $http)
    {
        if (empty($accessToken)) {
            throw new WabaException('Access token is required');
        }

        $this->token   = $accessToken;
        $this->http    = $http;
        $this->baseUrl = rtrim(config('waba.meta.graph.base_url'), '/');
        $this->version = config('waba.meta.graph.version');

        if (!$this->baseUrl || !$this->version) {
            throw new WabaException('Meta Graph configuration missing');
        }
    }

    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, [], $query);
    }

    public function post(string $path, array $payload = [], array $query = []): array
    {
        return $this->request('POST', $path, $payload, $query);
    }

    public function put(string $path, array $payload = [], array $query = []): array
    {
        return $this->request('PUT', $path, $payload, $query);
    }

    public function delete(string $path, array $query = []): array
    {
        return $this->request('DELETE', $path, [], $query);
    }

    public function multipart(string $path, array $data, array $query = []): array
    {
        if (!isset($data['file'])) {
            throw new WabaException('Multipart request requires file');
        }

        $file = $data['file'];

        $response = $this->baseRequest()
            ->attach(
                'file',
                $file,
                basename(stream_get_meta_data($file)['uri'] ?? 'upload.bin')
            )
            ->post($this->url($path), collect($data)->except('file')->toArray());

        return $this->handleResponse($response, $path, 'MULTIPART');
    }

    protected function request(
        string $method,
        string $path,
        array $payload = [],
        array $query = []
    ): array {
        $response = $this->baseRequest()->send(
            $method,
            $this->url($path),
            [
                'json'  => $payload ?: null,
                'query' => $query ?: null,
            ]
        );

        return $this->handleResponse($response, $path, $method);
    }

    protected function baseRequest()
    {
        return $this->http
            ->withToken($this->token)
            ->timeout(config('waba.http.timeout', 10))
            ->retry(
                config('waba.http.retry', 3),
                config('waba.http.retry_delay', 500),
                function ($exception, $request) {
                    return $this->shouldRetry($exception);
                }
            );
    }

    protected function shouldRetry($exception): bool
    {
        if (!method_exists($exception, 'response')) {
            return true;
        }

        $status = $exception->response?->status();

        // Retry kalau rate limit / server error
        return in_array($status, [429, 500, 502, 503, 504]);
    }

    protected function handleResponse(Response $response, string $path, string $method): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        $this->logError($response, $path, $method);

        throw $this->exceptionFromResponse($response);
    }

    protected function exceptionFromResponse(Response $response): WabaException
    {
        $message = $response->json('error.message')
            ?? $response->json('error.error_user_msg')
            ?? 'Meta Graph request failed';

        return new WabaException($message, $response->status());
    }

    protected function logError(Response $response, string $path, string $method): void
    {
        Log::error('WABA API ERROR', [
            'method' => $method,
            'path'   => $path,
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);
    }

    protected function url(string $path): string
    {
        return "{$this->baseUrl}/{$this->version}/{$path}";
    }
}
