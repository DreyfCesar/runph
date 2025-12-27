<?php

declare(strict_types=1);

namespace Runph\Services\Interpolator\Contracts;

interface InterpolatorInterface
{
    public function interpolate(string $input): string;
}
