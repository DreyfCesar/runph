<?php

declare(strict_types=1);

namespace Runph\Playbook\Contracts;

interface ModuleInterface
{
    public function run(): void;
}
