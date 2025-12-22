<?php

declare(strict_types=1);

namespace Runph\Services\Container;

use LogicException;
use Psr\Container\ContainerInterface;
use Runph\Services\Container\Contracts\FactoryContainerInterface;

class Container implements ContainerInterface, FactoryContainerInterface
{
    /** @var Array<object | class-string<object>> */
    private array $services = [];

    public function __construct(
        private ReflectionResolver $reflectionResolver
    ) {}

    /**
     * @param class-string<object> $id
     * @param object | class-string<object> $value
     */
    public function set(string $id, object|string $value): object|string
    {
        return $this->services[$id] = $value;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    public function get(string $id): mixed
    {
        $service = $id;

        if ($this->has($id)) {
            $result = $this->services[$id];

            if (! is_string($result)) {
                if (is_callable($result)) {
                    $value = $result();

                    if (! is_object($value)) {
                        throw new LogicException(sprintf('The returned value of function for "%s" must be an object, but got %s.', $id, gettype($value)));
                    }

                    $result = $this->set($id, $value);
                }

                /** @var T */
                return $result;
            }

            $service = $result;
        }

        /** @var T */
        return $this->set($id, $this->reflectionResolver->get($service));
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     * @param array<mixed, mixed> $parameters
     *
     * @return T
     */
    public function make(string $id, array $parameters): object
    {
        return $this->reflectionResolver->get($id, $parameters);
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }
}
