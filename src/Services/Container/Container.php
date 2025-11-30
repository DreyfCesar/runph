<?php

declare(strict_types=1);

namespace Runph\Services\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /** @var mixed[] */
    private array $services = [];

    public function set(string $id, mixed $value): void
    {
        $this->services[$id] = $value;
    }

    public function get(string $id)
    {
        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }
}
