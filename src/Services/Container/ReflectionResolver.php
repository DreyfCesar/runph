<?php

declare(strict_types=1);

namespace Runph\Services\Container;

use LogicException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use Runph\Services\Container\Exceptions\ServiceClassNotFoundException;
use Runph\Services\Container\Exceptions\UnresolvableDependencyException;
use Runph\Services\Container\Exceptions\UnsupportedIntersectionTypeException;

class ReflectionResolver
{
    private string $classname = '';
    private ContainerInterface $container;

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     * @param array<mixed, mixed> $parameters
     *
     * @return T
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
     * @param array<mixed, mixed> $parameters
     *
     * @return mixed[]
     */
    private function resolveConstructorParameters(ReflectionMethod $constructor, array $parameters): array
    {
        $params = [];

        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();
            $existsInParameters = array_key_exists($paramName, $parameters);

            if ($param->isDefaultValueAvailable() && ! $existsInParameters) {
                $params[] = $param->getDefaultValue();
                continue;
            }

            if ($paramType instanceof ReflectionIntersectionType) {
                throw new UnsupportedIntersectionTypeException($paramName, $this->classname);
            }

            if ($paramType instanceof ReflectionNamedType && ! $paramType->isBuiltin()) {
                $params[] = $this->container->get($paramType->getName());
                continue;
            }

            if ($existsInParameters) {
                $value = $parameters[$paramName];
                $hasMixedType = is_null($paramType);
                $matchedType = false;
                $enabledTypes = [];

                if ($paramType) {
                    if ($paramType instanceof ReflectionUnionType) {
                        foreach ($paramType->getTypes() as $type) {
                            if ($type instanceof ReflectionIntersectionType) {
                                throw new UnsupportedIntersectionTypeException($paramName, $this->classname);
                            }

                            $enabledTypes[] = $type->getName();
                        }
                    } else {
                        if (! $paramType instanceof ReflectionNamedType) {
                            throw new LogicException('Unexpected ReflectionType');
                        }

                        $enabledTypes[] = $paramType->getName();
                    }

                    if (! $hasMixedType) {
                        foreach ($enabledTypes as $type) {
                            if ($type === 'mixed') {
                                $hasMixedType = true;
                                break;
                            }

                            $typeCheckFn = "is_{$type}";
                            if (function_exists($typeCheckFn) && $typeCheckFn($value)) {
                                $matchedType = true;
                                break;
                            }
                        }
                    }
                }

                if ($hasMixedType || $matchedType) {
                    $params[] = $value;
                    continue;
                }
            }

            throw new UnresolvableDependencyException($paramName, $this->classname);
        }

        return $params;
    }
}
