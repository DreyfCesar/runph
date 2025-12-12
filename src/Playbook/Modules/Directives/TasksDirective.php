<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Directives;

use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Playbook\Exceptions\MissingModuleException;
use Runph\Playbook\Exceptions\MultipleModuleInTaskException;
use Runph\Playbook\Metadata\Modules\NameMeta;
use Runph\Playbook\Metadata\Modules\WhenMeta;
use Runph\Playbook\Metadata\Register;
use Runph\Playbook\ModuleRunner;
use Runph\Services\Config\ConfigLoader;

class TasksDirective implements ModuleInterface
{
    /** @var string[] */
    public const KEYWORDS = [
        'name',
        'when',
    ];

    /** @var array<string, class-string<ModuleInterface>> */
    private array $modules;

    /**
     * @param array<string, mixed>[] $value
     * @param ConfigLoader<string, class-string<ModuleInterface>> $configLoader
     */
    public function __construct(
        private array $value,
        private NameMeta $nameMeta,
        private WhenMeta $whenMeta,
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

            $this->nameMeta->run($register);
            $taskName = $register->name;

            $this->whenMeta->run($register);

            if ($register->shouldRun()) {
                $this->executeModule($task, $taskName);
            }
        }
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
