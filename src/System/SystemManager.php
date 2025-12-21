<?php

declare(strict_types=1);

namespace Runph\System;

use Runph\Services\Container\Container;
use Runph\Services\Container\ReflectionResolver;

class SystemManager
{
    private SystemData $data;

    public function __construct()
    {
        $reflection = new ReflectionResolver();
        $container = new Container($reflection);

        $reflection->setContainer($container);

        $this->data = new SystemData($container);
    }

    /**
     * @param SystemInterface[] $systems
     */
    public function run(array $systems): void
    {
        foreach ($systems as $system) {
            $system->execute($this->data);
        }
    }
}
