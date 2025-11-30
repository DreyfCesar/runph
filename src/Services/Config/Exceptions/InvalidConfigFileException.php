<?php

declare(strict_types=1);

namespace Runph\Services\Config\Exceptions;

class InvalidConfigFileException extends ConfigException
{
    public function __construct(string $path, mixed $returned)
    {
        parent::__construct("Config file '{$path}' must return an array, " . gettype($returned) . ' returned');
    }
}
