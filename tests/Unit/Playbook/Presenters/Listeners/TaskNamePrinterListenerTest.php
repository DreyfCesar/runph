<?php

declare(strict_types=1);

namespace Tests\Unit\Playbook\Presenters\Listeners;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Runph\Playbook\Metadata\Events\TaskNameChanged;
use Runph\Playbook\Presenters\Listeners\TaskNamePrinterListener;
use Runph\Playbook\Presenters\TaskPresenter;

class TaskNamePrinterListenerTest extends TestCase
{
    /** @var MockObject&TaskPresenter */
    private TaskPresenter $taskPresenter;

    private TaskNamePrinterListener $listener;

    protected function setUp(): void
    {
        $this->taskPresenter = $this->createMock(TaskPresenter::class);
        $this->listener = new TaskNamePrinterListener($this->taskPresenter);
    }

    #[DataProvider('namesProvider')]
    public function testHandlesName(string $taskName): void
    {
        $event = new TaskNameChanged($taskName);

        $this->taskPresenter
            ->expects($this->once())
            ->method('title')
            ->with($taskName);

        $this->listener->handle($event);
    }

    public function testHandleCanBeCalledMultipleTimes(): void
    {
        $this->taskPresenter
            ->expects($this->exactly(3))
            ->method('title');

        $this->listener->handle(new TaskNameChanged('First Task'));
        $this->listener->handle(new TaskNameChanged('Second Task'));
        $this->listener->handle(new TaskNameChanged('Third Task'));
    }

    public function testHandleDoesNotModifyEventName(): void
    {
        $originalName = 'Original Name';
        $event = new TaskNameChanged($originalName);

        $this->taskPresenter
            ->expects($this->once())
            ->method('title')
            ->with($originalName);

        $this->listener->handle($event);

        $this->assertSame($originalName, $event->name);
    }

    /**
     * @return string[][]
     */
    public static function namesProvider(): array
    {
        return [
            'example' => ['Deploy to production'],
            'empty task name' => [''],
            'special characters' => ['Task "with" special <chars> & symbols'],
            'unicode characters' => ['Tarea en español 中文 🚀'],
        ];
    }
}
