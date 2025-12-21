<?php

declare(strict_types=1);

namespace Runph\System;

interface SystemInterface
{
    public function execute(SystemData $data): void;
}
