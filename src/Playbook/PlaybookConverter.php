<?php

declare(strict_types=1);

namespace Runph\Playbook;

use Runph\Playbook\Exceptions\InvalidPlaybookException;
use Runph\Services\Filesystem\Filesystem;
use Runph\Services\Yaml\YamlHandler;

class PlaybookConverter
{
    public function __construct(
        private Filesystem $filesystem,
        private YamlHandler $yamlHandler,
    ) {}

    /**
     * @return array<mixed, mixed>
     */
    public function toArray(string $playbook): array
    {
        $this->filesystem->ensureReadable($playbook);

        $playbookContent = $this->yamlHandler->parseFile($playbook);

        if (! is_array($playbookContent)) {
            throw new InvalidPlaybookException("Invalid playbook '{$playbook}': expected YAML to parse into an array, got " . get_debug_type($playbookContent));
        }

        return $playbookContent;
    }
}
