<?php

declare(strict_types=1);

namespace Runph\Services\Filesystem;

use Runph\Services\Filesystem\Exceptions\FileNotFoundException;
use Runph\Services\Filesystem\Exceptions\FileNotReadableException;

class Filesystem
{
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public function ensureExists(string $path): void
    {
        if (! $this->exists($path)) {
            throw new FileNotFoundException($path);
        }
    }

    public function ensureReadable(string $path): void
    {
        $this->ensureExists($path);

        if (! $this->isReadable($path)) {
            throw new FileNotReadableException($path);
        }
    }

    public function requireFile(string $path): mixed
    {
        $this->ensureReadable($path);

        return require $path;
    }
}
