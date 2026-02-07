<?php

namespace Sejator\WabaSdk\Http;

use Illuminate\Support\Facades\Http;
use Sejator\WabaSdk\Exceptions\WabaException;

class WabaClient
{
    protected string $baseUrl;
    protected string $version;
    protected string $token;

    public function __construct(string $accessToken)
    {
        if (empty($accessToken)) {
            throw new WabaException('Access token is required');
        }

        $this->token   = $accessToken;
        $this->baseUrl = rtrim(config('waba.meta.graph.base_url'), '/');
        $this->version = config('waba.meta.graph.version');

        if (!$this->baseUrl || !$this->version) {
            throw new WabaException('Meta Graph configuration missing');
        }
    }


    // PUBLIC HTTP METHODS
    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, [], $query);
    }

    public function post(string $path, array $payload = [], array $query = []): array
    {
        return $this->request('POST', $path, $payload, $query);
    }

    public function delete(string $path, array $query = []): array
    {
        return $this->request('DELETE', $path, [], $query);
    }

    public function put(string $path, array $payload = [], array $query = []): array
    {
        return $this->request('PUT', $path, $payload, $query);
    }

    // MULTIPART
    public function multipart(string $path, array $data, array $query = []): array
    {
        if (!isset($data['file'])) {
            throw new WabaException('Multipart request requires file');
        }

        $file = $data['file'];

        $response = Http::withToken($this->token)
            ->timeout(30)
            ->retry(2, 200)
            ->attach(
                'file',
                $file,
                basename(stream_get_meta_data($file)['uri'])
            )
            ->post(
                $this->url($path),
                collect($data)->except('file')->toArray()
            );

        if ($response->failed()) {
            throw $this->exceptionFromResponse($response);
        }

        return $response->json();
    }

    // REQUEST
    protected function request(
        string $method,
        string $path,
        array $payload = [],
        array $query = []
    ): array {
        $response = Http::withToken($this->token)
            ->timeout(30)
            ->retry(2, 200)
            ->send(
                $method,
                $this->url($path),
                [
                    'json'  => $payload ?: null,
                    'query' => $query ?: null,
                ]
            );

        if ($response->failed()) {
            throw $this->exceptionFromResponse($response);
        }

        return $response->json();
    }

    protected function url(string $path): string
    {
        return "{$this->baseUrl}/{$this->version}/{$path}";
    }

    protected function exceptionFromResponse($response): WabaException
    {
        return new WabaException(
            $response->json('error.message', 'Meta Graph request failed'),
            $response->status()
        );
    }
}
