<?php

declare(strict_types=1);

namespace Runph\System\Service;

use Runph\Services\Container\Container;
use Runph\System\Service\Providers\ConfigServiceProvider;
use Runph\System\Service\Providers\ConsoleServiceProvider;
use Runph\System\Service\Providers\ContainerServiceProvider;
use Runph\System\Service\Providers\PlaybookServiceProvider;
use Runph\System\SystemInterface;

class ServiceSystem implements SystemInterface
{
    /** @var ServiceProviderInterface[] */
    private array $providers;

    public function __construct(string $configPath)
    {
        $this->providers = [
            new ContainerServiceProvider(),
            new ConsoleServiceProvider(),
            new ConfigServiceProvider($configPath),
            new PlaybookServiceProvider(),
        ];
    }

    public function execute(Container $container): void
    {
        foreach ($this->providers as $provider) {
            $provider->register($container);
        }
    }
}
