<?php

declare(strict_types=1);

namespace Runph\Services\Container\Contracts;

interface FactoryContainerInterface
{
    /**
     * @template T of object
     *
     * @param class-string<T> $id
     * @param array<mixed, mixed> $parameters
     *
     * @return T
     */
    public function make(string $id, array $parameters): object;
}
