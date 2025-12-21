<?php

declare(strict_types=1);

namespace Runph\System;

use Runph\Services\Container\Container;
use Runph\Services\Container\ReflectionResolver;

class SystemManager
{
    private Container $container;

    public function __construct()
    {
        $reflection = new ReflectionResolver();
        $this->container = new Container($reflection);

        $reflection->setContainer($this->container);
    }

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
