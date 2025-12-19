<?php

declare(strict_types=1);

use Runph\Playbook\Metadata\Handlers\ModuleHandler;
use Runph\Playbook\Metadata\Handlers\NameHandler;
use Runph\Playbook\Metadata\Handlers\WhenHandler;

return [
    'name' => NameHandler::class,
    'when' => WhenHandler::class,

    ModuleHandler::class,
];
