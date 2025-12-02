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
    private string $classname = '';

    public function __construct(
        private ContainerInterface $container,
    ) {}

    /**
     * @param array<string, mixed> $parameters
     */
    public function get(string $id, array $parameters = []): mixed
    {
        if (! class_exists($id)) {
            throw new ServiceClassNotFoundException($id);
        }

        $this->classname = $id;
        $reflection = new ReflectionClass($id);
        $constructor = $reflection->getConstructor();

        if (! $constructor) {
            return new $id();
        }

        $params = $this->resolveConstructorParameters($constructor, $parameters);

        return $reflection->newInstanceArgs($params);
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return mixed[]
     */
    private function resolveConstructorParameters(ReflectionMethod $constructor, array $parameters): array
    {
        $params = [];

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            $name = $param->getName();

            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    $params[] = $param->getDefaultValue();
                    continue;
                }

                throw new UnresolvableDependencyException($name, $this->classname);
            }

            $params[] = $this->container->get($type->getName());
        }

        return $params;
    }
}
