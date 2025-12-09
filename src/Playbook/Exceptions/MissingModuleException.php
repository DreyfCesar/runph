<?php

declare(strict_types=1);

namespace Runph\Playbook\Exceptions;

class MissingModuleException extends PlaybookException
{
    public function __construct(string $taskName)
    {
        parent::__construct("No module was specified for the task '{$taskName}'.");
    }
}
