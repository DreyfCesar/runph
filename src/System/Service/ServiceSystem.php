<?php

declare(strict_types=1);

namespace Runph\System\Service;

use Runph\System\Service\Providers\ConfigServiceProvider;
use Runph\System\Service\Providers\ConsoleServiceProvider;
use Runph\System\Service\Providers\ContainerServiceProvider;
use Runph\System\Service\Providers\ServiceProvider;
use Runph\System\SystemData;
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
            new ServiceProvider(),
        ];
    }

    public function execute(SystemData $data): void
    {
        foreach ($this->providers as $provider) {
            $provider->register($data->container());
        }
    }
}
