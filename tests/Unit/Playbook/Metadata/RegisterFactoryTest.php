<?php

declare(strict_types=1);

namespace Tests\Unit\Playbook\Metadata;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Runph\Playbook\Metadata\Register;
use Runph\Playbook\Metadata\RegisterFactory;

class RegisterFactoryTest extends TestCase
{
    /** @var MockObject&EventDispatcherInterface */
    private EventDispatcherInterface $eventDispatcher;

    private RegisterFactory $factory;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->factory = new RegisterFactory($this->eventDispatcher);
    }

    public function testMakeCreatesRegisterInstance(): void
    {
        $register = $this->factory->make([], 1);

        $this->assertInstanceOf(Register::class, $register);
    }

    public function testMakePassesDataToRegister(): void
    {
        $data = ['key' => 'value', 'count' => 42];

        $register = $this->factory->make($data, 1);

        $this->assertSame($data, $register->data());
    }

    public function testMakePassesIntegerIdToRegister(): void
    {
        $register = $this->factory->make([], 123);

        $this->assertSame(123, $register->identifier());
    }

    public function testMakePassesStringIdToRegister(): void
    {
        $register = $this->factory->make([], 'custom-id');

        $this->assertSame('custom-id', $register->identifier());
    }

    public function testMakeCreatesIndependentInstances(): void
    {
        $register1 = $this->factory->make(['id' => 1], 1);
        $register2 = $this->factory->make(['id' => 2], 2);

        $this->assertNotSame($register1, $register2);
        $this->assertSame(1, $register1->identifier());
        $this->assertSame(2, $register2->identifier());
    }

    public function testMakeHandlesEmptyData(): void
    {
        $register = $this->factory->make([], 0);

        $this->assertSame([], $register->data());
        $this->assertSame(0, $register->identifier());
    }

    public function testMakeHandlesComplexData(): void
    {
        $complexData = [
            'nested' => ['deep' => ['value' => 123]],
            'array' => [1, 2, 3],
            'mixed' => ['string', 42, true, null],
        ];

        $register = $this->factory->make($complexData, 'complex');

        $this->assertSame($complexData, $register->data());
    }

    public function testFactoryCanBeReusedMultipleTimes(): void
    {
        $registers = [];

        for ($i = 0; $i < 5; $i++) {
            $registers[] = $this->factory->make(['index' => $i], $i);
        }

        $this->assertCount(5, $registers);

        foreach ($registers as $index => $register) {
            $this->assertSame($index, $register->identifier());
            $this->assertSame($index, $register->get('index'));
        }
    }
}
