<?php

declare(strict_types=1);

namespace Runph\Playbook;

use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Services\Config\ConfigLoader;

class PlaybookExecutor
{
    public function __construct(
        private PlaybookConverter $playbookConverter,
        private ConfigLoader $configLoader,
        private ModuleRunner $moduleRunner,
    ) {}

    public function execute(string $filepath): void
    {
        $playbook = $this->playbookConverter->toArray($filepath);

        /** @var array<string, class-string<ModuleInterface>> */
        $directives = $this->configLoader->load('directives');

        $this->moduleRunner->run($playbook, $directives);
    }
}
