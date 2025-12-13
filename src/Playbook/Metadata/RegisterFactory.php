<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata;

class RegisterFactory
{
    /**
     * @param mixed[] $data
     */
    public function make(array $data, int|string $id): Register
    {
        return new Register($data, $id);
    }
}
