<?php

declare(strict_types=1);

use Runph\Playbook\Modules\Ask\AskTask;
use Runph\Playbook\Modules\Log\LogTask;

return [
    'log' => LogTask::class,
    'ask' => AskTask::class,
];
