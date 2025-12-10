<?php

declare(strict_types=1);

namespace Tests\Unit\Playbook\Modules;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Runph\Playbook\Modules\Log\LogTask;
use Symfony\Component\Console\Output\OutputInterface;

class LogTaskTest extends TestCase
{
    #[DataProvider('valuesForLogTaskProvider')]
    public function testLogTaskPrintTheValue(string $value): void
    {
        $output = $this->createMock(OutputInterface::class);
        $logTask = new LogTask($value, $output);
        $bufferedOutput = '';

        $output->expects($this->any())
            ->method('writeln')
            ->willReturnCallback(function (string $line) use (&$bufferedOutput) {
                $bufferedOutput .= "{$line}\n";
            });

        $logTask->run();

        $this->assertStringContainsString($value, $bufferedOutput);
    }

    /**
     * @return array<string, mixed[]>
     */
    public static function valuesForLogTaskProvider(): array
    {
        return [
            'spanish' => [
                'value' => 'abcdefghijklmnñopqrstuvwxyz0123456780áéíóú ',
            ],
        ];
    }
}
