<?php

declare(strict_types=1);

namespace Tests\Unit\Playbook\Metadata;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Runph\Playbook\Metadata\Events\TaskNameChanged;
use Runph\Playbook\Metadata\Register;

class RegisterTest extends TestCase
{
    /** @var MockObject&EventDispatcherInterface */
    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function testConstructorInitializesWithEmptyData(): void
    {
        $register = $this->createRegister([], 1);

        $this->assertSame([], $register->data());
        $this->assertSame(1, $register->identifier());
    }

    public function testConstructorInitializesWithProvidedData(): void
    {
        $data = ['key1' => 'value1', 'key2' => 42];
        $register = $this->createRegister($data, 'test-id');

        $this->assertSame($data, $register->data());
        $this->assertSame('test-id', $register->identifier());
    }

    public function testConstructorAcceptsIntegerId(): void
    {
        $register = $this->createRegister([], 123);

        $this->assertSame(123, $register->identifier());
    }

    public function testConstructorAcceptsStringId(): void
    {
        $register = $this->createRegister([], 'custom-id');

        $this->assertSame('custom-id', $register->identifier());
    }


    public function testHasReturnsTrueWhenKeyExists(): void
    {
        $register = $this->createRegister(['foo' => 'bar']);

        $this->assertTrue($register->has('foo'));
    }

    public function testHasReturnsFalseWhenKeyDoesNotExist(): void
    {
        $register = $this->createRegister(['foo' => 'bar']);

        $this->assertFalse($register->has('nonexistent'));
    }

    public function testHasReturnsFalseForEmptyData(): void
    {
        $register = $this->createRegister([]);

        $this->assertFalse($register->has('anything'));
    }

    public function testGetReturnsValueWhenKeyExists(): void
    {
        $register = $this->createRegister(['name' => 'John', 'age' => 30]);

        $this->assertSame('John', $register->get('name'));
        $this->assertSame(30, $register->get('age'));
    }

    public function testGetReturnsNullWhenKeyDoesNotExist(): void
    {
        $register = $this->createRegister(['foo' => 'bar']);

        $this->assertNull($register->get('nonexistent'));
    }

    public function testGetHandlesDifferentDataTypes(): void
    {
        $register = $this->createRegister([
            'string' => 'text',
            'int' => 42,
            'float' => 3.14,
            'bool' => true,
            'array' => [1, 2, 3],
            'null' => null,
        ]);

        $this->assertSame('text', $register->get('string'));
        $this->assertSame(42, $register->get('int'));
        $this->assertSame(3.14, $register->get('float'));
        $this->assertTrue($register->get('bool'));
        $this->assertSame([1, 2, 3], $register->get('array'));
        $this->assertNull($register->get('null'));
    }


    public function testRegisterPassesByDefault(): void
    {
        $register = $this->createRegister();

        $this->assertFalse($register->shouldSkip());
    }

    public function testSkipMarksRegisterToBeSkipped(): void
    {
        $register = $this->createRegister();

        $register->skip();

        $this->assertTrue($register->shouldSkip());
    }

    public function testPassMarksRegisterToNotBeSkipped(): void
    {
        $register = $this->createRegister();
        $register->skip();

        $register->pass();

        $this->assertFalse($register->shouldSkip());
    }

    public function testMultipleSkipCallsKeepRegisterSkipped(): void
    {
        $register = $this->createRegister();

        $register->skip();
        $register->skip();

        $this->assertTrue($register->shouldSkip());
    }

    public function testMultiplePassCallsKeepRegisterPassing(): void
    {
        $register = $this->createRegister();
        $register->skip();

        $register->pass();
        $register->pass();

        $this->assertFalse($register->shouldSkip());
    }

    public function testAlternatingPassAndSkip(): void
    {
        $register = $this->createRegister();

        $register->skip();
        $this->assertTrue($register->shouldSkip());

        $register->pass();
        $this->assertFalse($register->shouldSkip());

        $register->skip();
        $this->assertTrue($register->shouldSkip());
    }


    public function testNameIsEmptyByDefault(): void
    {
        $register = $this->createRegister();

        $this->assertSame('', $register->name());
    }

    public function testSetNameChangesName(): void
    {
        $register = $this->createRegister();

        $register->setName('Task Name');

        $this->assertSame('Task Name', $register->name());
    }

    public function testSetNameReturnsTheName(): void
    {
        $register = $this->createRegister();

        $result = $register->setName('New Name');

        $this->assertSame('New Name', $result);
    }

    public function testSetNameCanBeCalledMultipleTimes(): void
    {
        $register = $this->createRegister();

        $register->setName('First Name');
        $this->assertSame('First Name', $register->name());

        $register->setName('Second Name');
        $this->assertSame('Second Name', $register->name());

        $register->setName('Third Name');
        $this->assertSame('Third Name', $register->name());
    }

    public function testSetNameAcceptsEmptyString(): void
    {
        $register = $this->createRegister();
        $register->setName('Initial Name');

        $register->setName('');

        $this->assertSame('', $register->name());
    }

    public function testSetNameHandlesSpecialCharacters(): void
    {
        $register = $this->createRegister();
        $specialName = 'Task with "quotes" & special <chars>';

        $register->setName($specialName);

        $this->assertSame($specialName, $register->name());
    }

    public function testSetNameHandlesUnicodeCharacters(): void
    {
        $register = $this->createRegister();
        $unicodeName = 'Tarea en español 中文 🚀';

        $register->setName($unicodeName);

        $this->assertSame($unicodeName, $register->name());
    }


    public function testSetNameDispatchesTaskNameChangedEvent(): void
    {
        $register = $this->createRegister();
        $name = 'Event Test Task';

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (object $event) use ($name) {
                $this->assertInstanceOf(TaskNameChanged::class, $event);

                /** @var TaskNameChanged $event */
                $this->assertSame($name, $event->name);

                return true;
            }));

        $register->setName($name);
    }

    public function testSetNameDispatchesEventEachTime(): void
    {
        $register = $this->createRegister();

        $this->eventDispatcher
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturnCallback(function (TaskNameChanged $event) {
                $this->assertInstanceOf(TaskNameChanged::class, $event);
                return $event;
            });

        $register->setName('First');
        $register->setName('Second');
        $register->setName('Third');
    }

    public function testSetNameDispatchesEventWithCorrectNameSequence(): void
    {
        $register = $this->createRegister();
        $names = ['Alpha', 'Beta', 'Gamma'];
        $callCount = 0;

        $this->eventDispatcher
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturnCallback(function (TaskNameChanged $event) use ($names, &$callCount) {
                $this->assertSame($names[$callCount], $event->name);
                $callCount++;
                return $event;
            });

        foreach ($names as $name) {
            $register->setName($name);
        }
    }

    public function testCompleteWorkflowWithAllFeatures(): void
    {
        $data = ['priority' => 'high', 'assignee' => 'John'];
        $register = $this->createRegister($data, 'task-001');

        $this->assertSame('task-001', $register->identifier());
        $this->assertTrue($register->has('priority'));
        $this->assertSame('high', $register->get('priority'));
        $this->assertFalse($register->shouldSkip());

        $register->setName('Integration Test Task');
        $this->assertSame('Integration Test Task', $register->name());

        $register->skip();
        $this->assertTrue($register->shouldSkip());

        $register->pass();
        $this->assertFalse($register->shouldSkip());
    }

    public function testDataImmutabilityFromOutside(): void
    {
        $data = ['key' => 'value'];
        $register = $this->createRegister($data);

        $data['key'] = 'modified';
        $data['new_key'] = 'new_value';

        $this->assertSame('value', $register->get('key'));
        $this->assertFalse($register->has('new_key'));
    }

    /**
     * @param mixed[] $data
     */
    private function createRegister(array $data = [], int|string $id = 0): Register
    {
        return new Register($data, $id, $this->eventDispatcher);
    }
}
