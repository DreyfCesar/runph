<?php

declare(strict_types=1);

namespace Runph\Playbook\Exceptions;

class ModulesNotFoundException extends PlaybookException
{
    /**
     * @param string[] $modules
     */
    public function __construct(public readonly array $modules)
    {
        parent::__construct('Modules not found: ' . implode(', ', $modules));
    }
}
