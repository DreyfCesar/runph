<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata\Handlers;

use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Playbook\Exceptions\MissingModuleException;
use Runph\Playbook\Exceptions\MultipleModuleInTaskException;
use Runph\Playbook\Metadata\HandlerInterface;
use Runph\Playbook\Metadata\Register;
use Runph\Playbook\ModuleRunner;
use Runph\Services\Config\ConfigLoader;

class ModuleHandler implements HandlerInterface
{
    /** @var string[] */
    private array $keywords;

    /** @var array<string, class-string<ModuleInterface>> */
    private array $modules;

    /**
     * @param ConfigLoader<string, mixed> $configLoader
     */
    public function __construct(
        ConfigLoader $configLoader,
        private ModuleRunner $moduleRunner,
    ) {
        /** @var array<string, class-string<ModuleInterface>> */
        $modules = $configLoader->load('tasks');

        /** @var array<string, class-string<HandlerInterface>> */
        $handlers = $configLoader->load('meta_handlers');

        $this->keywords = array_keys(array_filter($handlers, 'is_string', ARRAY_FILTER_USE_KEY));
        $this->modules = $modules;
    }

    public function handle(Register $register): void
    {
        if (! $register->shouldRun()) {
            return;
        }

        $taskModules = array_diff_key($register->data(), array_flip($this->keywords));
        $modulesCount = count($taskModules);

        if ($modulesCount < 1) {
            throw new MissingModuleException($register->name());
        }

        if ($modulesCount > 1) {
            throw new MultipleModuleInTaskException($register->name());
        }

        $this->moduleRunner->run($taskModules, $this->modules);
    }
}
