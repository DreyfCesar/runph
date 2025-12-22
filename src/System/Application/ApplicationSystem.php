<?php

declare(strict_types=1);

namespace Runph\System\Application;

use Runph\Services\CommandsAutoloader;
use Runph\System\SystemData;
use Runph\System\SystemInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplicationSystem implements SystemInterface
{
    public function __construct(
        private string $name,
        private string $version,
    ) {}

    public function execute(SystemData $data): void
    {
        $container = $data->container();
        $commandsAutoloader = $container->get(CommandsAutoloader::class);

        $application = new Application($this->name, $this->version);
        $serviceProvider = new ApplicationServiceProvider($application);

        $commandsAutoloader->registerCommands($application);
        $serviceProvider->register($container);

        $application->run(
            input: $container->get(InputInterface::class),
            output: $container->get(OutputInterface::class),
        );
    }
}
