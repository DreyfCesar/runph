<?php

declare(strict_types=1);

namespace Runph\System\Service\Providers;

use Runph\Services\Config\ConfigLoader;
use Runph\Services\Container\Container;
use Runph\System\Service\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $configLoader = $container->get(ConfigLoader::class);

        /** @var array<class-string, class-string> */
        $services = $configLoader->load('services');

        foreach ($services as $id => $service) {
            $container->set($id, $service);
        }
    }
}
