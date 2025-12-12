<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata\Modules;

use Runph\Playbook\Exceptions\UnsupportedWhenTypeException;
use Runph\Playbook\Metadata\Register;

class WhenMeta
{
    public function run(Register $register): void
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
