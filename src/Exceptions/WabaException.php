<?php

namespace Sejator\WabaSdk\Exceptions;

use RuntimeException;

class WabaException extends RuntimeException
{
    protected int $status;

    protected array $errors;

    public function __construct(
        string $message,
        int $status = 500,
        array $errors = []
    ) {
        parent::__construct($message);

        $this->status = $status;
        $this->errors = $errors;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getMetaCode(): ?int
    {
        return data_get(
            $this->errors,
            'error.code'
        );
    }
}
