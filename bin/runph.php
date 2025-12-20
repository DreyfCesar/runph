#!/usr/bin/env php
<?php

declare(strict_types=1);

use Runph\Services\Container\Container;
use Runph\Services\Container\ReflectionResolver;
use Runph\System\Application\ApplicationSystem;
use Runph\System\Event\EventSystem;
use Runph\System\Service\ServiceSystem;
use Runph\System\SystemManager;

require __DIR__ . '/../vendor/autoload.php';

$reflection = new ReflectionResolver();
$container = new Container($reflection);
$systems = new SystemManager($container);

$reflection->setContainer($container);

$systems->run([
    new ServiceSystem(),
    new EventSystem(),
    new ApplicationSystem(),
]);
