<?php

declare(strict_types=1);

namespace Tests\Unit\Playbook\Metadata\Handlers;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Runph\Playbook\Contracts\TaskPresenterInterface;
use Runph\Playbook\Exceptions\InvalidRegisterValueException;
use Runph\Playbook\Metadata\Handlers\NameHandler;
use Runph\Playbook\Metadata\Register;

class NameHandlerTest extends TestCase
{
    /** @var MockObject&TaskPresenterInterface */
    private TaskPresenterInterface $presenter;

    /** @var MockObject&Register */
    private Register $register;

    private NameHandler $nameHandler;

    public function setUp(): void
    {
        $this->presenter = $this->createMock(TaskPresenterInterface::class);
        $this->register = $this->createMock(Register::class);
        $this->nameHandler = new NameHandler($this->presenter);
    }

    public function testHandleStoresProvidedNameAndSetsPresenterTitle(): void
    {
        $name = 'foo';

        $this->register
            ->expects($this->once())
            ->method('get')
            ->with('name')
            ->willReturn($name);

        $this->register
            ->expects($this->once())
            ->method('setName')
            ->with($name);

        $this->register
            ->expects($this->never())
            ->method('identifier');

        $this->presenter
            ->expects($this->once())
            ->method('title')
            ->with($name);

        $this->nameHandler->handle($this->register);
    }

    public function testHandleUsesIdentifierWhenNameDoesNotExist(): void
    {
        $identifier = 666;
        $expectedName = "#{$identifier}";

        $this->register
            ->expects($this->once())
            ->method('get')
            ->with('name')
            ->willReturn(null);

        $this->register
            ->expects($this->once())
            ->method('identifier')
            ->willReturn($identifier);

        $this->register
            ->expects($this->once())
            ->method('setName')
            ->with($expectedName);

        $this->presenter
            ->expects($this->once())
            ->method('title')
            ->with($expectedName);

        $this->nameHandler->handle($this->register);
    }

    public function testHandleThrowsExceptionWhenNameIsNotString(): void
    {
        $invalidName = 123;

        $this->register
            ->method('get')
            ->with('name')
            ->willReturn($invalidName);

        $this->expectException(InvalidRegisterValueException::class);

        $this->nameHandler->handle($this->register);
    }
}
