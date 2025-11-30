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
        if ($this->has($id)) {
            return $this->services[$id];
        }

        return $this->set($id, $this->reflectionResolver->get($id));
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }
}
