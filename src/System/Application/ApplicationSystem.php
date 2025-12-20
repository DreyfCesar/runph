<?php

declare(strict_types=1);

namespace Runph\System\Application;

use Runph\Services\CommandsAutoloader;
use Runph\Services\Container\Container;
use Runph\System\SystemInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplicationSystem implements SystemInterface
{
    public function execute(Container $container): void
    {
        $application = new Application('Runph', '[dev]');

        $commandsAutoloader = $container->get(CommandsAutoloader::class);
        $commandsAutoloader->registerCommands($application);

        $application->run(
            input: $container->get(InputInterface::class),
            output: $container->get(OutputInterface::class),
        );
    }
}
