<?php

declare(strict_types=1);

namespace Tests\Runph\Playbook\Modules\Directives;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Runph\Playbook\Modules\Directives\TasksDirective;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

final class TasksDirectiveTest extends TestCase
{
    /** @var MockObject&OutputInterface */
    private OutputInterface $output;

    /** @var MockObject&Terminal */
    private Terminal $terminal;

    public function setUp(): void
    {
        parent::setUp();

        $this->output = $this->createMock(OutputInterface::class);
        $this->terminal = $this->createMock(Terminal::class);
    }

    public function testPrintNameOrIdentifierOfTask(): void
    {
        $tasks = [
            ['name' => 'First task'],
            ['name' => 'Second task'],
            ['name' => 'áéíóúñÁÉÍÓÚÑ'],

            [],
            [],
        ];

        $expectedNames = [
            'First task',
            'Second task',
            'áéíóúñÁÉÍÓÚÑ',

            '#3',
            '#4',
        ];

        $tasksDirective = $this->createTasksDirective($tasks);
        $bufferedOutput = '';

        $this->output
            ->expects($this->any())
            ->method('writeln')
            ->willReturnCallback(function (string $line) use (&$bufferedOutput) {
                $bufferedOutput .= "{$line}\n";
            });

        $tasksDirective->run();

        foreach ($expectedNames as $name) {
            $this->assertStringContainsString($name, $bufferedOutput);
        }

    }

    /**
     * @param array<string, mixed>[] $tasks
     */
    private function createTasksDirective(array $tasks): TasksDirective
    {
        return new TasksDirective(
            $tasks,
            $this->output,
            $this->terminal,
        );
    }
}
