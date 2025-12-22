<?php

declare(strict_types=1);

namespace Runph\System\Application;

use Runph\Services\Container\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;

class ApplicationServiceProvider
{
    public function __construct(
        private Application $application,
    ) {}

    public function register(Container $container): void
    {
        $container->set(DebugFormatterHelper::class, fn () => $this->application->getHelperSet()->get('debug_formatter'));
        $container->set(FormatterHelper::class, fn () => $this->application->getHelperSet()->get('formatter'));
        $container->set(ProcessHelper::class, fn () => $this->application->getHelperSet()->get('process'));
        $container->set(QuestionHelper::class, fn () => $this->application->getHelperSet()->get('question'));
    }
}
