<?php

declare(strict_types=1);

namespace Runph\Services\Memory\Contracts;

interface MemoryInterface
{
    public function get(string $id, mixed $default = null): mixed;
    public function set(string $id, mixed $value): void;
    public function has(string $id): bool;
    public function delete(string $id): void;
}
