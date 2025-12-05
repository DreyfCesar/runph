<?php

declare(strict_types=1);

namespace Runph\Commands;

use InvalidArgumentException;
use Runph\Playbook\PlaybookExecutor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PlayCommand extends Command
{
    public function __construct(
        private PlaybookExecutor $playbookExecutor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('play')
            ->setDescription('Ejecuta un playbook')
            ->addArgument('file', InputArgument::REQUIRED, 'El archivo del playbook')
            ->setHelp('Procesa y ejecuta un playbook');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $playbook = $input->getArgument('file');

        if (! is_string($playbook)) {
            throw new InvalidArgumentException('The "file" argument must be a string');
        }

        $this->playbookExecutor->execute(
            filepath: $playbook,
        );

        return Command::SUCCESS;
    }
}
