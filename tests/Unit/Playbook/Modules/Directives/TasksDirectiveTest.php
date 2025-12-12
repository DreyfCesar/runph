<?php

declare(strict_types=1);

namespace Tests\Runph\Playbook\Modules\Directives;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Playbook\Exceptions\MissingModuleException;
use Runph\Playbook\Exceptions\MultipleModuleInTaskException;
use Runph\Playbook\Exceptions\UnsupportedWhenTypeException;
use Runph\Playbook\Metadata\Handlers\NameHandler;
use Runph\Playbook\Metadata\Handlers\WhenHandler;
use Runph\Playbook\ModuleRunner;
use Runph\Playbook\Modules\Directives\TasksDirective;
use Runph\Services\Config\ConfigLoader;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

final class TasksDirectiveTest extends TestCase
{
    /** @var MockObject&OutputInterface */
    private OutputInterface $output;

    /** @var MockObject&Terminal */
    private Terminal $terminal;

    /** @var MockObject&ModuleRunner */
    private ModuleRunner $moduleRunner;

    /** @var MockObject&ConfigLoader<string, class-string<ModuleInterface>> */
    private ConfigLoader $configLoader;

    public function setUp(): void
    {
        parent::setUp();

        $this->output = $this->createMock(OutputInterface::class);
        $this->terminal = $this->createMock(Terminal::class);
        $this->moduleRunner = $this->createMock(ModuleRunner::class);
        $this->configLoader = $this->createMock(ConfigLoader::class);
    }

    public function testThrowsExceptionWhenTaskHasMultipleModules(): void
    {
        $tasks = [
            ['module_1' => [], 'module_2' => []],
        ];

        $this->expectException(MultipleModuleInTaskException::class);

        $tasksDirective = $this->createTasksDirective($tasks);
        $tasksDirective->run();
    }

    public function testThrowsExceptionWhenTaskIsMissingModule(): void
    {
        $tasks = [
            [],
        ];

        $this->expectException(MissingModuleException::class);

        $tasksDirective = $this->createTasksDirective($tasks);
        $tasksDirective->run();
    }

    public function testCallsModuleRunnerToExecuteTheModules(): void
    {
        $taskPayload = [];
        $enabledModules = [];

        $tasks = [
            ['module_1' => 'value to the the module'],
            ['module_2' => [true]],
        ];

        $this->configLoader
            ->method('load')
            ->with('tasks')
            ->willReturn($enabledModules);

        $this->moduleRunner
            ->expects($this->any())
            ->method('run')
            ->willReturnCallback(function ($taskModule, $moduleList) use (&$taskPayload, $enabledModules) {
                $taskPayload[] = $taskModule;
                $this->assertSame($enabledModules, $moduleList);
            });

        $tasksDirective = $this->createTasksDirective($tasks);
        $tasksDirective->run();

        $this->assertSame($tasks, $taskPayload);
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

        $tasks = array_map(fn ($task) => array_merge($task, ['fake_module' => true]), $tasks);
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
     * @param array<string, mixed> $task
     */
    #[DataProvider('whenConditionalProvider')]
    public function testModuleRunsBasedOnWhenCondition(array $task, bool $pass): void
    {
        $tasks = [['fake_module' => 'something'] + $task];

        $this->moduleRunner
            ->expects($pass ? $this->once() : $this->never())
            ->method('run');

        $tasksDirective = $this->createTasksDirective($tasks);

        $tasksDirective->run();
    }

    /**
     * @return array<string, mixed[]>
     */
    public static function whenConditionalProvider(): array
    {
        return [
            'no when condition' => [
                'task' => [],
                'pass' => true,
            ],
            'boolean true' => [
                'task' => ['when' => true],
                'pass' => true,
            ],
            'boolean false' => [
                'task' => ['when' => false],
                'pass' => false,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $task
     */
    #[DataProvider('invalidWhenTypeProvider')]
    public function testThrowsExceptionForUnsupportedWhenType(array $task): void
    {
        $tasks = [['fake_module' => 'something'] + $task];
        $tasksDirective = $this->createTasksDirective($tasks);

        $this->expectException(UnsupportedWhenTypeException::class);

        $tasksDirective->run();
    }

    /**
     * @return array<string, mixed[]>
     */
    public static function invalidWhenTypeProvider(): array
    {
        return [
            'string example' => [
                'task' => ['when' => 'foo'],
            ],
        ];
    }

    /**
     * @param array<string, mixed>[] $tasks
     */
    private function createTasksDirective(array $tasks): TasksDirective
    {
        return new TasksDirective(
            $tasks,
            new NameHandler($this->terminal, $this->output),
            new WhenHandler(),
            $this->moduleRunner,
            $this->configLoader,
        );
    }
}
