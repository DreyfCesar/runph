<?php

declare(strict_types=1);

namespace Runph\Playbook;

interface ModuleInterface
{
    public function key(): string;
    public function execute(): void;
}
