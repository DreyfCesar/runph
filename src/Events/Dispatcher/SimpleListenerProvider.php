<?php

declare(strict_types=1);

namespace Runph\Events\Dispatcher;

use LogicException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class SimpleListenerProvider implements ListenerProviderInterface
{
    /** @var array<class-string, class-string[]> */
    private array $listeners;

    public function __construct(
        private ContainerInterface $container,
    ) {}

    /**
     * @param class-string $eventClass
     * @param class-string $listenerClass
     */
    public function addListener(string $eventClass, string $listenerClass): void
    {
        $this->listeners[$eventClass][] = $listenerClass;
    }

    /**
     * @return iterable<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventClass = get_class($event);

        if (! isset($this->listeners[$eventClass])) {
            return [];
        }

        foreach ($this->listeners[$eventClass] as $listenerClass) {
            /** @var object */
            $listener = $this->container->get($listenerClass);

            if (! method_exists($listener, 'handle')) {
                throw new LogicException("Listener '{$listenerClass}' must have a handle() method");
            }

            yield fn (object $event) => $listener->handle($event);
        }
    }
}
