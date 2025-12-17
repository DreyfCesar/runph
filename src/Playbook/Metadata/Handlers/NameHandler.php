<?php

declare(strict_types=1);

namespace Runph\Playbook\Metadata\Handlers;

use Runph\Playbook\Exceptions\InvalidRegisterValueException;
use Runph\Playbook\Metadata\HandlerInterface;
use Runph\Playbook\Metadata\Register;

class NameHandler implements HandlerInterface
{
    public function handle(Register $register): void
    {
        $name = $register->get('name');

        if (is_null($name)) {
            $name = '#' . $register->identifier();
        }

        if (! is_string($name)) {
            throw new InvalidRegisterValueException('The name must be a string, got ' . gettype($name));
        }

        $register->setName($name);
    }
}
