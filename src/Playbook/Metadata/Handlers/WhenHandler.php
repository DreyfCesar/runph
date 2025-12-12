<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata\Handlers;

use Runph\Playbook\Exceptions\UnsupportedWhenTypeException;
use Runph\Playbook\Metadata\HandlerInterface;
use Runph\Playbook\Metadata\Register;

class WhenHandler implements HandlerInterface
{
    public function handle(Register $register): void
    {
        $condition = $register->get('when');

        if (! is_null($condition)) {
            if (is_bool($condition)) {
                $register->shouldRun = $condition;
                return;
            }

            throw new UnsupportedWhenTypeException(gettype($condition));
        }

        $register->shouldRun = true;
    }
}
