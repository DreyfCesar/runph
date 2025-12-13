<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata\Handlers;

use Runph\Playbook\Metadata\HandlerInterface;
use Runph\Playbook\Metadata\Register;
use Runph\Playbook\Presenters\TaskPresenter;

class NameHandler implements HandlerInterface
{
    public function __construct(
        private TaskPresenter $taskPresenter,
    ) {}

    public function handle(Register $register): void
    {
        $name = $register->get('name');

        if (! is_string($name)) {
            $name = '#' . $register->identifier();
        }

        $register->setName($name);

        $this->taskPresenter->title($name);
    }
}
