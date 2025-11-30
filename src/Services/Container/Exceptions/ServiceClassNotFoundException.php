<?php

declare(strict_types=1);

namespace Runph\Services\Container\Exceptions;

class ServiceClassNotFoundException extends ContainerException
{
    public function __construct(string $serviceClass)
    {
        parent::__construct("No entry was found for the identifier '{$serviceClass}'.");
    }
}
