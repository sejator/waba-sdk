<?php

namespace Sejator\WabaSdk\Traits;

use Illuminate\Support\Facades\Http;

trait HasHttpRequests
{
    protected function get(string $url, array $query = []): array
    {
        return Http::withToken($this->token())
            ->get($url, $query)
            ->throw()
            ->json();
    }

    protected function post(string $url, array $data = []): array
    {
        return Http::withToken($this->token())
            ->post($url, $data)
            ->throw()
            ->json();
    }

    protected function delete(string $url, array $data = []): array
    {
        return Http::withToken($this->token())
            ->delete($url, $data)
            ->throw()
            ->json();
    }
}
