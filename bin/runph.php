#!/usr/bin/env php
<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Runph\Events\Dispatcher\SimpleEventDispatcher;
use Runph\Events\Dispatcher\SimpleListenerProvider;
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

$listenerProvider = new SimpleListenerProvider($container);
$eventDispatcher = new SimpleEventDispatcher($listenerProvider);
$configLoader = new ConfigLoader($container->get(Filesystem::class), dirname(__DIR__) . '/config');

$container->set(ListenerProviderInterface::class, $listenerProvider);
$container->set(EventDispatcherInterface::class, $eventDispatcher);
$container->set(ConfigLoader::class, $configLoader);

/** @var array<class-string<object>, string|list<class-string<object>>> */
$listenerList = $configLoader->load('listeners');

foreach ($listenerList as $eventClass => $listeners) {
    if (is_string($listeners)) {
        $listeners = [$listeners];
    }

    foreach ($listeners as $listener) {
        /** @var class-string<object> $listener */
        $listenerProvider->addListener($eventClass, $listener);
    }
}

$application = new Application('Runph', '[dev]');

$commandsAutoloader = $container->get(CommandsAutoloader::class);
$commandsAutoloader->registerCommands($application);

$application->run(
    input: $container->get(InputInterface::class),
    output: $container->get(OutputInterface::class),
);
