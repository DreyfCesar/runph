<?php

declare(strict_types=1);

namespace Runph\System;

use Runph\Services\Container\Container;

class SystemManager
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * @param SystemInterface[] $systems
     */
    public function run(array $systems): void
    {
        foreach ($systems as $system) {
            $system->execute($this->container);
        }
    }
}
