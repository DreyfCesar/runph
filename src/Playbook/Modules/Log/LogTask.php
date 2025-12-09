<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Log;

use Runph\Playbook\Contracts\ModuleInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogTask implements ModuleInterface
{
    public function __construct(
        private readonly string $value,
        private OutputInterface $output,
    ) {}

    public function run(): void
    {
        $this->output->writeln('');
        $this->output->writeln("<info>Log: </> {$this->value}");
    }
}
