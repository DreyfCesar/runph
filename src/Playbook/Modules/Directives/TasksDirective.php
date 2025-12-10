<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Directives;

use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Playbook\Exceptions\MissingModuleException;
use Runph\Playbook\Exceptions\MultipleModuleInTaskException;
use Runph\Playbook\ModuleRunner;
use Runph\Services\Config\ConfigLoader;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class TasksDirective implements ModuleInterface
{
    /** @var string[] */
    public const KEYWORDS = [
        'name',
    ];

    /** @var array<string, class-string<ModuleInterface>> */
    private array $modules;

    /**
     * @param array<string, mixed>[] $value
     */
    public function __construct(
        private array $value,
        private OutputInterface $output,
        private Terminal $terminal,
        private ModuleRunner $moduleRunner,
        ConfigLoader $configLoader,
    ) {
        /** @var array<string, class-string<ModuleInterface>> */
        $modules = $configLoader->load('tasks');
        $this->modules = $modules;
    }

    public function run(): void
    {
        foreach ($this->value as $id => $task) {
            $taskName = $this->getAndPrintName($task, $id);
            $this->executeModule($task, $taskName);
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

    /**
     * @param array<string, mixed> $task
     */
    public function executeModule(array $task, string $taskName): void
    {
        $taskModules = array_diff_key($task, array_flip(self::KEYWORDS));
        $modulesCount = count($taskModules);

        if ($modulesCount < 1) {
            throw new MissingModuleException($taskName);
        }

        if ($modulesCount > 1) {
            throw new MultipleModuleInTaskException($taskName);
        }

        $this->moduleRunner->run($taskModules, $this->modules);
    }
}
