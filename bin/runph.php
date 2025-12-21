#!/usr/bin/env php
<?php

declare(strict_types=1);

use Runph\System\Application\ApplicationSystem;
use Runph\System\Event\EventSystem;
use Runph\System\Service\ServiceSystem;
use Runph\System\SystemManager;

require __DIR__ . '/../vendor/autoload.php';

$systems = new SystemManager();

$systems->run([
    new ServiceSystem(
        configPath: dirname(__DIR__) . '/config',
    ),
    new EventSystem(
        listenersConfigFile: 'listeners',
    ),
    new ApplicationSystem(
        name: 'Runph',
        version: '[dev]',
    ),
]);
