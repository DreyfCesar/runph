<?php

declare(strict_types=1);

namespace Runph\Services\Interpolator;

use Runph\Services\Interpolator\Contracts\InterpolatorInterface;
use Runph\Services\Memory\Contracts\MemoryInterface;
use RuntimeException;

class SimpleInterpolator implements InterpolatorInterface
{
    public const string PATTERN = '/(?<!\\\\)\$\{(\w+)\}|(?<!\\\\)\$(\w+)/';

    public function __construct(
        private readonly MemoryInterface $memory,
    ) {}

    public function interpolate(string $input): string
    {
        $result = preg_replace_callback(self::PATTERN, fn ($matches) => $this->replaceVariable($matches), $input);

        if ($result === null) {
            throw new RuntimeException('Failed to interpolate string: PCRE error occurred');
        }

        return str_replace(['\${', '\$'], ['${', '$'], $result);
    }

    /**
     * @param string[] $matches
     */
    private function replaceVariable(array $matches): string
    {
        $varname = $matches[1] ?: $matches[2];

        if (!$this->memory->has($varname)) {
            return $matches[0];
        }

        $value = $this->memory->get($varname);

        if (!is_string($value) && !is_numeric($value)) {
            throw new RuntimeException(
                sprintf(
                    'Variable "%s" must be string or numeric, %s given',
                    $varname,
                    gettype($value)
                )
            );
        }

        return (string) $value;
    }
}
