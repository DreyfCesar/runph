<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata\Events;

use Psr\EventDispatcher\StoppableEventInterface;
use Runph\Events\Traits\StoppableEvent;

class TaskNameChanged implements StoppableEventInterface
{
    use StoppableEvent;

    public function __construct(
        public readonly string $name,
    ) {}
}
