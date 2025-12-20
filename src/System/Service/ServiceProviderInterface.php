<?php

declare(strict_types=1);

namespace Runph\System\Service;

use Runph\Services\Container\Container;

interface ServiceProviderInterface
{
    public function register(Container $container): void;
}
