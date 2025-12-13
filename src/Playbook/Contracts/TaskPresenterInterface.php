<?php

declare(strict_types=1);

namespace Runph\Playbook\Contracts;

interface TaskPresenterInterface
{
    public function title(string $title): void;
}
