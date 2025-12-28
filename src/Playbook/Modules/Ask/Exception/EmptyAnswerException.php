<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Ask\Exception;

class EmptyAnswerException extends AskException
{
    protected $message = 'Answer cannot be empty';
}
