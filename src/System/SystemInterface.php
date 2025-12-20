<?php

declare(strict_types=1);

namespace Runph\System;

use Runph\Services\Container\Container;

interface SystemInterface
{
    public function execute(Container $container): void;
}
