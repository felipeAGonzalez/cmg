<?php

namespace App\Exceptions;

use RuntimeException;

class CmgDelegatedAuthException extends RuntimeException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct('CMG delegated authentication emission failed: '.$reason);
    }
}
