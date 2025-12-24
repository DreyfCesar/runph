<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Ask\Exception;

class InvalidAnswerException extends AskException
{
    public function __construct(mixed $answer)
    {
        parent::__construct('The answer must be a string, got ' . gettype($answer));
    }
}
