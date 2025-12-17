<?php

declare(strict_types=1);

namespace Runph\Playbook\Presenters\Listeners;

use Runph\Playbook\Metadata\Events\TaskNameChanged;
use Runph\Playbook\Presenters\TaskPresenter;

class TaskNamePrinterListener
{
    public function __construct(
        private TaskPresenter $taskPresenter,
    ) {}

    public function handle(TaskNameChanged $event): void
    {
        $this->taskPresenter->title($event->name);
    }
}
