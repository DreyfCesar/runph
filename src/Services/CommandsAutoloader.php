<?php

declare(strict_types=1);

namespace Runph\Services;

use Psr\Container\ContainerInterface;
use Runph\Services\Config\ConfigLoader;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

class CommandsAutoloader
{
    public function __construct(
        private ConfigLoader $config,
        private ContainerInterface $container,
    ) {}

    public function registerCommands(Application $application): void
    {
        /** @var string[] */
        $commands = $this->config->load('commands');

        foreach ($commands as $commandClassname) {
            $command = $this->container->get($commandClassname);

            if (! $command instanceof Command) {
                throw new RuntimeException("The entry '{$commandClassname}' must be an instance of " . Command::class);
            }

            $application->addCommand($command);
        }
    }
}
