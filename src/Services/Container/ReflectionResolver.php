<?php

declare(strict_types=1);

namespace Runph\Services\Container;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Runph\Services\Container\Exceptions\ServiceClassNotFoundException;
use Runph\Services\Container\Exceptions\UnresolvableDependencyException;

class ReflectionResolver
{
    private string $current = '';

    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function get(string $id): mixed
    {
        if (! class_exists($id)) {
            throw new ServiceClassNotFoundException($id);
        }

        $this->current = $id;
        $reflection = new ReflectionClass($id);
        $constructor = $reflection->getConstructor();

        if (! $constructor) {
            return new $id();
        }

        $params = $this->resolveConstructorParameters($constructor);

        return $reflection->newInstanceArgs($params);
    }

    /**
     * @return mixed[]
     */
    private function resolveConstructorParameters(ReflectionMethod $constructor): array
    {
        $params = [];

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    $params[] = $param->getDefaultValue();
                    continue;
                }

                throw new UnresolvableDependencyException($param->getName(), $this->current);
            }

            $params[] = $this->container->get($type->getName());
        }

        return $params;
    }
}
