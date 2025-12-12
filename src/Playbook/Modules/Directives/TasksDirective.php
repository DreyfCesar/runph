<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Directives;

use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Playbook\Exceptions\MissingModuleException;
use Runph\Playbook\Exceptions\MultipleModuleInTaskException;
use Runph\Playbook\Metadata\MetaHandler;
use Runph\Playbook\Metadata\Register;
use Runph\Playbook\ModuleRunner;
use Runph\Services\Config\ConfigLoader;

class TasksDirective implements ModuleInterface
{
    /** @var array<string, class-string<ModuleInterface>> */
    private array $modules;

    /**
     * @param array<string, mixed>[] $value
     * @param ConfigLoader<string, class-string<ModuleInterface>> $configLoader
     */
    public function __construct(
        private array $value,
        private MetaHandler $metaHandler,
        private ModuleRunner $moduleRunner,
        ConfigLoader $configLoader,
    ) {
        $modules = $configLoader->load('tasks');
        $this->modules = $modules;
    }

    public function run(): void
    {
        foreach ($this->value as $id => $task) {
            $register = new Register($task, $id);

            $this->metaHandler->run($register);

            if ($register->shouldRun()) {
                $this->executeModule($task, $register->name);
            }
        }
    }

    /**
     * @param array<string, mixed> $task
     */
    public function executeModule(array $task, string $taskName): void
    {
        $taskModules = array_diff_key($task, array_flip($this->metaHandler->keywords()));
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
