<?php

declare(strict_types=1);

namespace Runph\System\Service\Providers;

use Runph\Services\Config\ConfigLoader;
use Runph\Services\Container\Container;
use Runph\Services\Filesystem\Filesystem;
use Runph\System\Service\ServiceProviderInterface;

class ConfigServiceProvider implements ServiceProviderInterface
{
    public function __construct(
        private string $configPath,
    ) {}

    public function register(Container $container): void
    {
        $container->set(ConfigLoader::class, new ConfigLoader(
            $container->get(Filesystem::class),
            $this->configPath
        ));
    }
}
