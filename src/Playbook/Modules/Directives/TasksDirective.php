<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Directives;

use Runph\Playbook\Contracts\ModuleInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TasksDirective implements ModuleInterface
{
    /**
     * @param array<string, mixed>[] $value
     */
    public function __construct(
        private array $value,
        private OutputInterface $output,
    ) {}

    public function run(): void
    {
        foreach ($this->value as $id => $task) {
            $this->output->writeln("Task #{$id}");
        }
    }
}
