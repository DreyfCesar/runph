#!/usr/bin/env php
<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Runph\Services\Container\Container;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

// Settings
$container = new Container();
$container->set(ContainerInterface::class, $container);

$application = new Application('Runph', '[dev]');

$application->run();
