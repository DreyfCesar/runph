<?php

declare(strict_types=1);

namespace Runph\Services\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /** @var mixed[] */
    private array $services = [];

    private ReflectionResolver $reflectionResolver;

    public function __construct()
    {
        $this->reflectionResolver = new ReflectionResolver($this);
    }

    public function set(string $id, mixed $value): mixed
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
        return $this->resolve($id);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     * @param array<string, mixed> $parameters
     *
     * @return T
     */
    public function make(string $id, array $parameters): mixed
    {
        return $this->resolve($id, $parameters);
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     * @param array<string, mixed> $parameters
     *
     * @return T
     */
    private function resolve(string $id, array $parameters = []): mixed
    {
        $service = null;

        if ($this->has($id)) {
            $service = $this->services[$id];

            assert($service instanceof $id);
            return $service;
        }

        $service = $this->set($id, $this->reflectionResolver->get($id, $parameters));
        assert($service instanceof $id);

        return $service;
    }
}
