<?php

declare(strict_types=1);

use Runph\Playbook\Contracts\TaskPresenterInterface;
use Runph\Playbook\Presenters\TaskPresenter;
use Runph\Services\Memory\Contracts\MemoryInterface;
use Runph\Services\Memory\SharedMemory;

return [
    TaskPresenterInterface::class => TaskPresenter::class,
    MemoryInterface::class => SharedMemory::class,
];
