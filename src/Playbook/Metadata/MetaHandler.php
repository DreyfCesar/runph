<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata;

use Psr\Container\ContainerInterface;
use Runph\Playbook\Exceptions\InvalidHandlerException;
use Runph\Services\Config\ConfigLoader;

class MetaHandler
{
    /** @var HandlerInterface[] */
    private array $handlers;

    /**
     * @param ConfigLoader<string, class-string<HandlerInterface>> $configLoader
     */
    public function __construct(
        ConfigLoader $configLoader,
        ContainerInterface $container,
    ) {
        $handlers = $configLoader->load('meta_handlers');

        foreach ($handlers as $handlerClassname) {
            $instance = $container->get($handlerClassname);

            if (! $instance instanceof HandlerInterface) {
                $interface = HandlerInterface::class;
                $givenType = gettype($instance);

                throw new InvalidHandlerException("Handler must implement {$interface}, given {$givenType}.");
            }

            $this->handlers[] = $instance;
        }
    }

    public function run(Register $register): void
    {
        foreach ($this->handlers as $handler) {
            if ($register->shouldSkip()) {
                break;
            }

            $handler->handle($register);
        }
    }
}
