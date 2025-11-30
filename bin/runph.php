#!/usr/bin/env php
<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Runph\Services\CommandsAutoloader;
use Runph\Services\Config\ConfigLoader;
use Runph\Services\Container\Container;
use Runph\Services\Filesystem\Filesystem;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

// Settings
$container = new Container();
$container->set(ContainerInterface::class, $container);

$container->set(
    ConfigLoader::class, 
    new ConfigLoader(
        $container->get(Filesystem::class),
        dirname(__DIR__) . '/config'
    )
);

$application = new Application('Runph', '[dev]');

$commandsAutoloader = $container->get(CommandsAutoloader::class);
$commandsAutoloader->registerCommands($application);

$application->run();
