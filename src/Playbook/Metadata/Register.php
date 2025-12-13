<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata;

class Register
{
    public string $name = '';
    public bool $shouldRun = false;

    /**
     * @param mixed[] $data
     */
    public function __construct(
        private array $data,
        private int|string $id,
    ) {}

    public function get(string $address): mixed
    {
        return $this->data[$address] ?? null;
    }

    public function identifier(): int|string
    {
        return $this->id;
    }

    public function shouldRun(): bool
    {
        return $this->shouldRun;
    }

    /**
     * @return mixed[]
     */
    public function data(): array
    {
        return $this->data;
    }

    public function name(): string
    {
        return $this->name;
    }
}
