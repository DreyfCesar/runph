<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Directives;

use Runph\Playbook\Modules\AbstractModule;
use Symfony\Component\Console\Output\OutputInterface;

class NameDirective extends AbstractModule
{
    protected string $key = 'name';

    public function __construct(
        private string $value,
        private OutputInterface $output,
    ) {}

    public function execute(): void
    {
        $this->output->writeln($this->value);
    }
}
