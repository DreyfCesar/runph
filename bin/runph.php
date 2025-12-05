#!/usr/bin/env php
<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Runph\Services\CommandsAutoloader;
use Runph\Services\Config\ConfigLoader;
use Runph\Services\Container\Container;
use Runph\Services\Container\Contracts\FactoryContainerInterface;
use Runph\Services\Container\ReflectionResolver;
use Runph\Services\Filesystem\Filesystem;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

require __DIR__ . '/../vendor/autoload.php';

// Settings
$reflection = new ReflectionResolver();
$container = new Container($reflection);

$reflection->setContainer($container);
$container->set(ContainerInterface::class, $container);
$container->set(FactoryContainerInterface::class, $container);
$container->set(InputInterface::class, new ArgvInput());
$container->set(OutputInterface::class, new ConsoleOutput());

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

$application->run(
    input: $container->get(InputInterface::class),
    output: $container->get(OutputInterface::class),
);
