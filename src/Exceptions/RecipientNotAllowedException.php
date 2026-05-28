<?php

namespace Sejator\WabaSdk\Exceptions;

class RecipientNotAllowedException extends GraphApiException
{
    public function isTestingRecipient(): bool
    {
        return $this->getMetaCode() === 131030;
    }
}
