<?php

declare(strict_types=1);

namespace Runph\System\Service\Providers;

use Runph\Playbook\Contracts\TaskPresenterInterface;
use Runph\Playbook\Presenters\TaskPresenter;
use Runph\Services\Container\Container;
use Runph\System\Service\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(TaskPresenterInterface::class, TaskPresenter::class);
    }
}
