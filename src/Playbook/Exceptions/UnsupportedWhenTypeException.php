<?php

declare(strict_types=1);

namespace Runph\Playbook\Exceptions;

class UnsupportedWhenTypeException extends PlaybookException
{
    public function __construct(string $type)
    {
        parent::__construct("Unsupported type for 'when' conditional. Got: {$type}");
    }
}
