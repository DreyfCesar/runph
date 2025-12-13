<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Directives;

use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Playbook\Metadata\MetaHandler;
use Runph\Playbook\Metadata\Register;

class TasksDirective implements ModuleInterface
{
    /**
     * @param array<string, mixed>[] $value
     */
    public function __construct(
        private array $value,
        private MetaHandler $metaHandler,
    ) {}

    public function run(): void
    {
        foreach ($this->value as $id => $task) {
            $register = new Register($task, $id);

            $this->metaHandler->run($register);
        }
    }
}
