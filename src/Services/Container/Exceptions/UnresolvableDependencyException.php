<?php

declare(strict_types=1);

namespace Runph\Services\Container\Exceptions;

class UnresolvableDependencyException extends ContainerException
{
    public function __construct(string $paramName, string $serviceName)
    {
        parent::__construct("Cannot resolve the parameter '{$paramName}' for service '{$serviceName}'");
    }
}
