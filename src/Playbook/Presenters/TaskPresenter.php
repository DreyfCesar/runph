<?php

declare(strict_types=1);

namespace Runph\Playbook\Presenters;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class TaskPresenter
{
    public function __construct(
        private Terminal $terminal,
        private OutputInterface $output,
    ) {}

    public function title(string $title): void
    {
        $label = "TASK {$title}";
        $width = $this->terminal->getWidth();
        $stars = max(0, $width - strlen($label) - 1);

        $this->output->writeln('');
        $this->output->writeln("<info>{$label}</> " . str_repeat('*', $stars));
    }
}
