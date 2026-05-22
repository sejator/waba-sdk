<?php

namespace Sejator\WabaSdk\Contracts;

interface WebhookManager
{
    public function subscribe(string $wabaId): array;

    public function unsubscribe(string $wabaId): array;
}
