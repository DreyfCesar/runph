<?php

declare(strict_types=1);

namespace Runph\System\Service;

use Psr\Container\ContainerInterface;
use Runph\Playbook\Contracts\TaskPresenterInterface;
use Runph\Playbook\Presenters\TaskPresenter;
use Runph\Services\Config\ConfigLoader;
use Runph\Services\Container\Container;
use Runph\Services\Container\Contracts\FactoryContainerInterface;
use Runph\Services\Filesystem\Filesystem;
use Runph\System\SystemInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ServiceSystem implements SystemInterface
{
    public function execute(Container $container): void
    {
        $container->set(ContainerInterface::class, $container);
        $container->set(FactoryContainerInterface::class, $container);
        $container->set(InputInterface::class, new ArgvInput());
        $container->set(OutputInterface::class, new ConsoleOutput());
        $container->set(ConfigLoader::class, new ConfigLoader($container->get(Filesystem::class), dirname(dirname(dirname(__DIR__))) . '/config'));

        $container->set(TaskPresenterInterface::class, TaskPresenter::class);
    }
}
