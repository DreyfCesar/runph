<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules;

use Runph\Playbook\ModuleInterface;

abstract class AbstractModule implements ModuleInterface
{
    protected string $key = '';

    public function key(): string
    {
        return $this->key;
    }
}
