<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata\Handlers;

use Runph\Playbook\Contracts\TaskPresenterInterface;
use Runph\Playbook\Metadata\HandlerInterface;
use Runph\Playbook\Metadata\Register;

class NameHandler implements HandlerInterface
{
    public function __construct(
        private TaskPresenterInterface $taskPresenter,
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
