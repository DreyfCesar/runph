<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata;

class Register
{
    private string $name = '';
    private bool $pass = true;

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

    public function pass(): void
    {
        $this->pass = true;
    }

    public function skip(): void
    {
        $this->pass = false;
    }

    public function shouldSkip(): bool
    {
        return ! $this->pass;
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

    public function setName(string $name): string
    {
        return $this->name = $name;
    }
}
