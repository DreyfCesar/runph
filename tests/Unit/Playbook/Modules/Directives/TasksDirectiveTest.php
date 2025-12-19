<?php

declare(strict_types=1);

namespace Tests\Unit\Playbook\Modules\Directives;

use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Runph\Playbook\Metadata\MetaHandler;
use Runph\Playbook\Metadata\Register;
use Runph\Playbook\Metadata\RegisterFactory;
use Runph\Playbook\Modules\Directives\TasksDirective;

class TasksDirectiveTest extends TestCase
{
    /** @var MockObject&MetaHandler */
    private MetaHandler $metaHandler;

    /** @var MockObject&RegisterFactory */
    private RegisterFactory $registerFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metaHandler = $this->createMock(MetaHandler::class);
        $this->registerFactory = $this->createMock(RegisterFactory::class);
    }

    public function testRunProcessesAllTasksInOrder(): void
    {
        $tasks = [
            'task_1' => ['name' => 'First Task', 'command' => 'echo "first"'],
            'task_2' => ['name' => 'Second Task', 'command' => 'echo "second"'],
            'task_3' => ['name' => 'Third Task', 'command' => 'echo "third"'],
        ];

        $register1 = $this->createMock(Register::class);
        $register2 = $this->createMock(Register::class);
        $register3 = $this->createMock(Register::class);

        $factoryCallCount = 0;

        $this->registerFactory->expects($this->exactly(3))
            ->method('make')
            ->willReturnCallback(function (array $task, string|int $id) use ($tasks, $register1, $register2, $register3, &$factoryCallCount): Register {
                /** @var array<string, mixed> $task */

                $factoryCallCount++;

                match ($factoryCallCount) {
                    1 => $this->assertTaskData($tasks['task_1'], 'task_1', $task, $id, $register1),
                    2 => $this->assertTaskData($tasks['task_2'], 'task_2', $task, $id, $register2),
                    3 => $this->assertTaskData($tasks['task_3'], 'task_3', $task, $id, $register3),
                    default => throw new LogicException('Unexpected call count'),
                };

                return match ($factoryCallCount) {
                    1 => $register1,
                    2 => $register2,
                    3 => $register3,
                    default => throw new LogicException('Unexpected call count'),
                };
            });

        $handlerCallCount = 0;

        $this->metaHandler->expects($this->exactly(3))
            ->method('run')
            ->willReturnCallback(function (Register $register) use ($register1, $register2, $register3, &$handlerCallCount): void {
                $handlerCallCount++;

                $expected = match ($handlerCallCount) {
                    1 => $register1,
                    2 => $register2,
                    3 => $register3,
                    default => throw new \LogicException('Unexpected call count'),
                };

                $this->assertSame($expected, $register);
            });

        $directive = new TasksDirective($tasks, $this->metaHandler, $this->registerFactory);
        $directive->run();
    }

    public function testRunWithEmptyTasksArray(): void
    {
        $tasks = [];

        $this->registerFactory->expects($this->never())
            ->method('make');

        $this->metaHandler->expects($this->never())
            ->method('run');

        $directive = new TasksDirective($tasks, $this->metaHandler, $this->registerFactory);
        $directive->run();
    }

    public function testRunWithSingleTask(): void
    {
        $tasks = [
            'single_task' => ['name' => 'Only Task', 'command' => 'ls'],
        ];

        $register = $this->createMock(Register::class);

        $this->registerFactory->expects($this->once())
            ->method('make')
            ->with($tasks['single_task'], 'single_task')
            ->willReturn($register);

        $this->metaHandler->expects($this->once())
            ->method('run')
            ->with($register);

        $directive = new TasksDirective($tasks, $this->metaHandler, $this->registerFactory);
        $directive->run();
    }

    public function testRunPreservesTaskArrayKeys(): void
    {
        $tasks = [
            'install' => ['command' => 'npm install'],
            'build' => ['command' => 'npm build'],
            'deploy' => ['command' => 'npm deploy'],
        ];

        $expectedKeys = ['install', 'build', 'deploy'];
        /** @var array<int|string> $actualKeys */
        $actualKeys = [];

        $this->registerFactory->expects($this->exactly(3))
            ->method('make')
            ->willReturnCallback(function (array $task, string|int $id) use (&$actualKeys): Register {
                $actualKeys[] = $id;
                return $this->createMock(Register::class);
            });

        $this->metaHandler->expects($this->exactly(3))
            ->method('run');

        $directive = new TasksDirective($tasks, $this->metaHandler, $this->registerFactory);
        $directive->run();

        $this->assertSame($expectedKeys, $actualKeys);
    }

    public function testRunWithNumericKeys(): void
    {
        $tasks = [
            0 => ['name' => 'Task Zero'],
            1 => ['name' => 'Task One'],
            2 => ['name' => 'Task Two'],
        ];

        /** @var array<int|string> $capturedIds */
        $capturedIds = [];

        $this->registerFactory->expects($this->exactly(3))
            ->method('make')
            ->willReturnCallback(function (array $task, string|int $id) use (&$capturedIds): Register {
                $capturedIds[] = $id;
                return $this->createMock(Register::class);
            });

        $this->metaHandler->expects($this->exactly(3))
            ->method('run');

        $directive = new TasksDirective($tasks, $this->metaHandler, $this->registerFactory);
        $directive->run();

        $this->assertSame([0, 1, 2], $capturedIds);
    }

    public function testRunWithComplexTaskData(): void
    {
        $tasks = [
            'complex_task' => [
                'name' => 'Complex Task',
                'command' => 'echo "test"',
                'when' => true,
                'tags' => ['deployment', 'production'],
                'vars' => ['env' => 'prod'],
                'retry' => 3,
            ],
        ];

        $register = $this->createMock(Register::class);

        $this->registerFactory->expects($this->once())
            ->method('make')
            ->with(
                $this->identicalTo($tasks['complex_task']),
                'complex_task'
            )
            ->willReturn($register);

        $this->metaHandler->expects($this->once())
            ->method('run')
            ->with($register);

        $directive = new TasksDirective($tasks, $this->metaHandler, $this->registerFactory);
        $directive->run();
    }

    public function testRunContinuesProcessingEvenIfMetaHandlerDoesNotThrow(): void
    {
        $tasks = [
            'task_1' => ['command' => 'first'],
            'task_2' => ['command' => 'second'],
            'task_3' => ['command' => 'third'],
        ];

        $this->registerFactory->expects($this->exactly(3))
            ->method('make')
            ->willReturn($this->createMock(Register::class));

        $this->metaHandler->expects($this->exactly(3))
            ->method('run');

        $directive = new TasksDirective($tasks, $this->metaHandler, $this->registerFactory);
        $directive->run();

        $this->addToAssertionCount(1);
    }

    /**
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $actual
     */
    private function assertTaskData(array $expected, string $expectedId, array $actual, string|int $actualId, Register $returnRegister): Register
    {
        $this->assertSame($expected, $actual);
        $this->assertSame($expectedId, $actualId);
        return $returnRegister;
    }
}
