<?php

declare(strict_types=1);

namespace Runph\Playbook;

use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Playbook\Exceptions\ModulesNotFoundException;
use Runph\Services\Container\Contracts\FactoryContainerInterface;

class ModuleRunner
{
    public function __construct(
        private FactoryContainerInterface $factory,
    ) {}

    /**
     * @param mixed[] $definition
     * @param array<string, class-string<ModuleInterface>> $modules
     */
    public function run(array $definition, array $modules): void
    {
        $missingModules = array_keys(array_diff_key($definition, $modules));

        if (! empty($missingModules)) {
            throw new ModulesNotFoundException($missingModules);
        }

        foreach ($definition as $moduleName => $value) {
            $parameters = is_array($value) ? $value : [];
            $parameters['value'] = $value;

            $module = $this->factory->make($modules[$moduleName], $parameters);

            $module->run();
        }
    }
}
