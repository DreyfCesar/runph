#!/usr/bin/env php
<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Runph\Services\Config\ConfigLoader;
use Runph\Services\Container\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

require __DIR__ . '/../vendor/autoload.php';

// Settings
$container = new Container();
$container->set(ContainerInterface::class, $container);

/** @var ConfigLoader */
$config = $container->set(ConfigLoader::class, new ConfigLoader(dirname(__DIR__) . '/config'));

$application = new Application('Runph', '[dev]');

/** @var string[] */
$commands = $config->load('commands');

foreach ($commands as $commandClassname) {
    $command = $container->get($commandClassname);

    if (! $command instanceof Command) {
        throw new RuntimeException("The entry '{$commandClassname}' must be an instance of " . Command::class);
    }

    $application->addCommand($command);
}

$application->run();
