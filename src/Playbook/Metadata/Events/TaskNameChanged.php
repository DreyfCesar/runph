<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata\Events;

class TaskNameChanged
{
    public function __construct(
        public readonly string $name,
    ) {}
}
