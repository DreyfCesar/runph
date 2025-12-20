<?php

declare(strict_types=1);

namespace Runph\System\Service\Providers;

use Psr\Container\ContainerInterface;
use Runph\Services\Container\Container;
use Runph\Services\Container\Contracts\FactoryContainerInterface;
use Runph\System\Service\ServiceProviderInterface;

class ContainerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(ContainerInterface::class, $container);
        $container->set(FactoryContainerInterface::class, $container);
    }
}
