<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata;

interface MetaHandlerInterface
{
    public function run(Register $register): void;
}
