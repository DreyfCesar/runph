<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata\Handlers;

use Runph\Playbook\Metadata\HandlerInterface;
use Runph\Playbook\Metadata\Register;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class NameHandler implements HandlerInterface
{
    public function __construct(
        private Terminal $terminal,
        private OutputInterface $output,
    ) {}

    public function handle(Register $register): void
    {
        $name = $register->get('name');

        if (! is_string($name)) {
            $name = '#' . $register->identifier();
        }

        $label = "TASK {$name}";
        $width = $this->terminal->getWidth();
        $stars = max(0, $width - strlen($label) - 1);

        $register->name = $name;
        $this->output->writeln('');
        $this->output->writeln("<info>{$label}</> " . str_repeat('*', $stars));
    }
}
