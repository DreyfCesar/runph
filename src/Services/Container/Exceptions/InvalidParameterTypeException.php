<?php

declare(strict_types=1);

namespace Runph\Services\Container\Exceptions;

use ReflectionParameter;

class InvalidParameterTypeException extends ContainerException
{
    public readonly string $paramName;
    public readonly string $className;
    public readonly string $paramType;

    /**
     * @param string[] $expectedTypes
     */
    public function __construct(ReflectionParameter $parameter, public readonly array $expectedTypes, string $givenType)
    {
        $this->paramType = $givenType;
        $this->paramName = $parameter->getName();
        $this->className = $parameter->getDeclaringClass()?->getName() ?? '';

        parent::__construct(sprintf(
            "Type mismatch for parameter '\$%s' in %s::__construct().\nExpected type(s): %s.\nGiven: %s.",
            $this->paramName,
            $this->className,
            implode('|', $this->expectedTypes),
            $this->paramType,
        ));
    }
}
