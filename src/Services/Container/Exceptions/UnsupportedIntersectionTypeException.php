<?php

declare(strict_types=1);

namespace Runph\Services\Container\Exceptions;

class UnsupportedIntersectionTypeException extends ContainerException
{
    public function __construct(string $parameterName, string $className)
    {
        parent::__construct("Intersection types are not supported for parameter \${$parameterName} in {$className}");
    }
}
