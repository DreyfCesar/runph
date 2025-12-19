<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Directives;

use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Playbook\Metadata\MetaHandler;
use Runph\Playbook\Metadata\RegisterFactory;

class TasksDirective implements ModuleInterface
{
    /**
     * @param array<string, mixed>[] $value Tasks
     */
    public function __construct(
        private array $value,
        private MetaHandler $metaHandler,
        private RegisterFactory $registerFactory,
    ) {}

    public function run(): void
    {
        foreach ($this->value as $id => $task) {
            $register = $this->registerFactory->make($task, $id);

            $this->metaHandler->run($register);
        }
    }
}
