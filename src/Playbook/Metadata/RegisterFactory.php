<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata;

use Psr\EventDispatcher\EventDispatcherInterface;

class RegisterFactory
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    /**
     * @param mixed[] $data
     */
    public function make(array $data, int|string $id): Register
    {
        return new Register($data, $id, $this->eventDispatcher);
    }
}
