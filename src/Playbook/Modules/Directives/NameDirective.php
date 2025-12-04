<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Directives;

use Runph\Playbook\Contracts\ModuleInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NameDirective implements ModuleInterface
{
    public function __construct(
        private string $value,
        private OutputInterface $output,
    ) {}

    public function run(): void
    {
        $this->output->writeln($this->value);
    }
}
