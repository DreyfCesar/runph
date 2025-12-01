<?php

declare(strict_types=1);

namespace Runph\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PlayCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('play')
            ->setDescription('Ejecuta un playbook')
            ->addArgument('file', InputArgument::REQUIRED, 'El archivo del playbook')
            ->setHelp('Procesa y ejecuta un playbook');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hola mundo');
        return Command::SUCCESS;
    }
}
