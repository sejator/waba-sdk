<?php

namespace Sejator\WabaSdk\Services;

use Illuminate\Support\Facades\Http;
use Sejator\WabaSdk\Exceptions\WabaException;

class Client
{
    public function __construct(
        protected string $baseUrl,
        protected string $token
    ) {}

    public function get(string $uri, array $query = [])
    {
        return $this->handle(
            $this->http()->get($this->url($uri), $query)
        );
    }

    public function post(string $uri, array $payload = [])
    {
        return $this->handle(
            $this->http()->post($this->url($uri), $payload)
        );
    }

    public function delete(string $uri, array $payload = [])
    {
        return $this->handle(
            $this->http()->delete($this->url($uri), $payload)
        );
    }

    public function postWithQuery(string $uri, array $query = [])
    {
        return $this->handle(
            $this->http()
                ->withOptions(['query' => $query])
                ->post($this->url($uri))
        );
    }

    public function multipart(string $uri, array $multipart): array
    {
        $http = $this->http();

        foreach ($multipart as $part) {
            $http = $http->attach(
                $part['name'],
                $part['contents'],
                $part['filename'] ?? null
            );
        }

        return $this->handle($http->post($this->url($uri)));
    }

    public function uploadBinary(string $uploadId, string $binary)
    {
        return $this->handle(
            $this->http()
                ->withHeaders([
                    'file_offset' => 0,
                    'Content-Type' => 'application/octet-stream',
                ])
                ->send('POST', $this->url($uploadId), [
                    'body' => $binary,
                ])
        );
    }

    public function getRaw(string $url)
    {
        $res = $this->http()->get($url);

        if ($res->successful()) {
            return $res->body();
        }

        throw new WabaException('Failed to download media');
    }

    protected function http()
    {
        return Http::withToken($this->token);
    }

    protected function url(string $uri): string
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($uri, '/');
    }

    protected function handle($res)
    {
        if ($res->successful()) {
            return $res->json();
        }

        $json = $res->json();

        $fullMessage = collect([
            "message"  => data_get($json, 'error.message'),
            "type"     => data_get($json, 'error.type'),
            "code"     => data_get($json, 'error.code'),
            "subcode"  => data_get($json, 'error.error_subcode'),
            "details"  => data_get($json, 'error.error_data.details'),
            "user_msg" => data_get($json, 'error.error_user_msg'),
        ])
            ->filter()
            ->map(fn($v, $k) => strtoupper($k) . ": " . $v)
            ->implode(" | ");

        throw new WabaException($fullMessage ?: $res->body());
    }
}
