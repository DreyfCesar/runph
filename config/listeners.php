<?php

declare(strict_types=1);

use Runph\Playbook\Metadata\Events\TaskNameChanged;
use Runph\Playbook\Presenters\Listeners\TaskNamePrinterListener;

/** @var array<class-string<object>, string | list<class-string<object>>> */
return [
    TaskNameChanged::class => TaskNamePrinterListener::class,
];
