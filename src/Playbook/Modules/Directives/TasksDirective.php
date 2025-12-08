<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Directives;

use Runph\Playbook\Contracts\ModuleInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class TasksDirective implements ModuleInterface
{
    /**
     * @param array<string, mixed>[] $value
     */
    public function __construct(
        private array $value,
        private OutputInterface $output,
        private Terminal $terminal,
    ) {}

    public function run(): void
    {
        foreach ($this->value as $id => $task) {
            $this->getAndPrintName($task, $id);
        }
    }

    /**
     * @param array<string, mixed> $task
     */
    private function getAndPrintName(array $task, int $id): string
    {
        $name = ! empty($task['name']) && is_string($task['name'])
            ? "[{$task['name']}]"
            : "#{$id}";

        $label = "TASK {$name}";
        $width = $this->terminal->getWidth();
        $stars = max(0, $width - strlen($label) - 1);

        $this->output->writeln('');
        $this->output->writeln("<info>{$label}</> " . str_repeat('*', $stars));

        return $name;
    }
}
