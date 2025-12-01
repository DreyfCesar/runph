<?php

declare(strict_types=1);

namespace Runph\Services\Yaml;

use Symfony\Component\Yaml\Yaml;

class YamlHandler
{
    public function parseFile(string $file): mixed
    {
        return Yaml::parseFile($file);
    }
}
