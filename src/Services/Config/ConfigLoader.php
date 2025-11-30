<?php

declare(strict_types=1);

namespace Runph\Services\Config;

use RuntimeException;

class ConfigLoader
{
    public function __construct(
        private string $path,
    ) {
        $realPath = realpath($path);

        if ($realPath === false || ! is_readable($realPath)) {
            throw new RuntimeException("Config root path '{$path}' does not exist or is not readable");
        }
    }

    /**
     * @return array<mixed, mixed>
     */
    public function load(string $file): array
    {
        if (! str_ends_with($file, '.php')) {
            $file .= '.php';
        }

        $path = $this->path . DIRECTORY_SEPARATOR . $file;

        if (! file_exists($path) || ! is_readable($path)) {
            throw new RuntimeException("Config file '{$file}' not found in '{$this->path}'");
        }

        $loadFile = static function (string $fullpath) {
            return require $fullpath;
        };

        $config = $loadFile($path);

        if (! is_array($config)) {
            throw new RuntimeException("Config file '{$path}' must return an array, " . gettype($config) . ' returned');
        }

        return $config;
    }
}
