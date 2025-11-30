<?php

declare(strict_types=1);

namespace Runph\Services\Container;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

class ReflectionResolver
{
    private string $current = '';

    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function get(string $id): mixed
    {
        if (! class_exists($id)) {
            throw new InvalidArgumentException("No entry was found for the identifier '{$id}' in the " . self::class);
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

                throw new RuntimeException("Cannot resolve the parameter '{$param->getName()}' for service '{$this->current}'");
            }

            $params[] = $this->container->get($type->getName());
        }

        return $params;
    }
}
