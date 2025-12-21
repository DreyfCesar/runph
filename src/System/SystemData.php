<?php

declare(strict_types=1);

namespace Runph\System;

use Runph\Services\Container\Container;

class SystemData
{
    public function __construct(
        private Container $container,
    ) {}

    public function container(): Container
    {
        return $this->container;
    }
}
