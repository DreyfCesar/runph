<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata;

class Register
{
    private string $name = '';
    private bool $shouldRunModule = true;

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

    public function shouldRunModule(): bool
    {
        return $this->shouldRunModule;
    }

    public function skipModule(): void
    {
        $this->shouldRunModule = false;
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
