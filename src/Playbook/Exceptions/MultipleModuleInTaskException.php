<?php

declare(strict_types=1);

namespace Runph\Playbook\Exceptions;

class MultipleModuleInTaskException extends PlaybookException
{
    public function __construct(string $taskName)
    {
        parent::__construct("The task '{$taskName}' has more than one module");
    }
}
