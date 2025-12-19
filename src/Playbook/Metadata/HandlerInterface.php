<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata;

interface HandlerInterface
{
    public function handle(Register $register): void;
}
