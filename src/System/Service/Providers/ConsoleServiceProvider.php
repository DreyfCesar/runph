<?php

declare(strict_types=1);

namespace Runph\System\Service\Providers;

use Runph\Services\Container\Container;
use Runph\System\Service\ServiceProviderInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(InputInterface::class, new ArgvInput());
        $container->set(OutputInterface::class, new ConsoleOutput());
    }
}
