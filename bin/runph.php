#!/usr/bin/env php
<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Runph\Commands\Play\PlayCommand;
use Runph\Services\Container\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

require __DIR__ . '/../vendor/autoload.php';

// Settings
$container = new Container();
$container->set(ContainerInterface::class, $container);

$application = new Application('Runph', '[dev]');

$defaultCommands = [
    PlayCommand::class,
];

foreach ($defaultCommands as $commandClassname) {
    $command = $container->get($commandClassname);

    if (! $command instanceof Command) {
        throw new RuntimeException("The entry '{$commandClassname}' must be an instance of " . Command::class);
    }

    $application->addCommand($command);
}

$application->run();
