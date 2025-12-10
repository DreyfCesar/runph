<?php

declare(strict_types=1);

namespace Runph\Services\Config;

use Runph\Services\Config\Exceptions\InvalidConfigFileException;
use Runph\Services\Filesystem\Filesystem;

/**
 * @template TKey
 * @template TValue
 */
class ConfigLoader
{
    public function __construct(
        private Filesystem $filesystem,
        private string $path,
    ) {
        $fullpath = realpath($path);

        if ($fullpath === false) {
            $fullpath = $path;
        }

        $this->filesystem->ensureReadable($fullpath);
        $this->path = $fullpath;
    }

    /**
     * @return array<TKey, TValue>
     */
    public function load(string $file): array
    {
        if (! str_ends_with($file, '.php')) {
            $file .= '.php';
        }

        $path = $this->path . DIRECTORY_SEPARATOR . $file;
        $config = $this->filesystem->requireFile($path);

        if (! is_array($config)) {
            throw new InvalidConfigFileException($path, $config);
        }

        /** @var array<TKey, TValue> */
        return $config;
    }
}
