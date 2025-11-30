<?php

declare(strict_types=1);

namespace Runph\Services\Filesystem\Exceptions;

class FileNotFoundException extends FilesystemException
{
    public function __construct(string $path)
    {
        parent::__construct("The file '{$path}' does not exist.");
    }
}
