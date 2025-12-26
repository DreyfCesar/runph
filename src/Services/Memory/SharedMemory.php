<?php

declare(strict_types=1);

namespace Runph\Services\Memory;

use Runph\Services\Memory\Contracts\MemoryInterface;

class SharedMemory implements MemoryInterface
{
    /**
     * @param mixed[] $memory
     */
    public function __construct(
        private array $memory = [],
    ) {}

    public function get(string $id, mixed $default = null): mixed
    {
        return $this->memory[$id] ?? $default;
    }

    public function set(string $id, mixed $value): void
    {
        $this->memory[$id] = $value;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->memory);
    }

    public function delete(string $id): void
    {
        unset($this->memory[$id]);
    }
}
