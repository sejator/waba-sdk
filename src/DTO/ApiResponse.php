<?php

namespace Sejator\WabaSdk\DTO;

class ApiResponse
{
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public ?string $message = null,
        public int $status = 200,
    ) {}

    public static function success(mixed $data = null, ?string $message = null): self
    {
        return new self(true, $data, $message);
    }

    public static function failed(string $message, int $status = 400): self
    {
        return new self(false, null, $message, $status);
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'status' => $this->status,
        ];
    }
}
