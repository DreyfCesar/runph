<?php

declare(strict_types=1);

namespace Runph\Events\Dispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class SimpleEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private ListenerProviderInterface $listenerProvider,
    ) {}

    /**
     * @param object $event
     *
     * @return object
     */
    public function dispatch(object $event): object
    {
        if ($this->shouldStopPropagation($event)) {
            return $event;
        }

        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            /** @var callable $listener */

            $listener($event);

            if ($this->shouldStopPropagation($event)) {
                break;
            }
        }

        return $event;
    }

    /**
     * @phpstan-impure
     */
    private function shouldStopPropagation(object $event): bool
    {
        return $event instanceof StoppableEventInterface && $event->isPropagationStopped();
    }
}
