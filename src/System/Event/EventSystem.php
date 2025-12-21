<?php

declare(strict_types=1);

namespace Runph\System\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Runph\Events\Dispatcher\SimpleEventDispatcher;
use Runph\Events\Dispatcher\SimpleListenerProvider;
use Runph\Services\Config\ConfigLoader;
use Runph\System\SystemData;
use Runph\System\SystemInterface;

class EventSystem implements SystemInterface
{
    public function __construct(
        private string $listenersConfigFile,
    ) {}

    public function execute(SystemData $data): void
    {
        $container = $data->container();
        $listenerProvider = new SimpleListenerProvider($container);
        $eventDispatcher = new SimpleEventDispatcher($listenerProvider);
        $configLoader = $container->get(ConfigLoader::class);

        $container->set(ListenerProviderInterface::class, $listenerProvider);
        $container->set(EventDispatcherInterface::class, $eventDispatcher);

        /** @var array<class-string<object>, string|list<class-string<object>>> */
        $listenerList = $configLoader->load($this->listenersConfigFile);

        foreach ($listenerList as $eventClass => $listeners) {
            if (is_string($listeners)) {
                $listeners = [$listeners];
            }

            foreach ($listeners as $listener) {
                /** @var class-string<object> $listener */
                $listenerProvider->addListener($eventClass, $listener);
            }
        }
    }
}
