<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Directives;

use Runph\Playbook\Contracts\ModuleInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class NameDirective implements ModuleInterface
{
    public function __construct(
        private string $value,
        private OutputInterface $output,
        private Terminal $terminal,
    ) {}

    public function run(): void
    {
        $width = $this->terminal->getWidth();
        $line = str_repeat('─', max(10, $width - 2));

        $this->output->writeln('');
        $this->output->writeln("<info>{$line}</info>");
        $this->output->writeln("<comment> ▶  Playbook:</comment> <info>{$this->value}</info>");
        $this->output->writeln("<info>{$line}</info>");
        $this->output->writeln('');
    }
}
