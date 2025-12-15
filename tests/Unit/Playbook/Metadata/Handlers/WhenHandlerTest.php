<?php

declare(strict_types=1);

namespace Tests\Unit\Playbook\Metadata\Handlers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Runph\Playbook\Exceptions\UnsupportedWhenTypeException;
use Runph\Playbook\Metadata\Handlers\WhenHandler;
use Runph\Playbook\Metadata\Register;

class WhenHandlerTest extends TestCase
{
    /**
     * @param array<string, mixed> $value
     */
    #[DataProvider('trueWhenProvider')]
    public function testHandleCallsPassWhenWhenIsTrue(mixed $value): void
    {
        $register = $this->createMock(Register::class);
        $handler = new WhenHandler();

        $register->expects($this->once())
            ->method('pass');

        $register->expects($this->once())
            ->method('get')
            ->with('when')
            ->willReturn($value);

        $register->expects($this->never())
            ->method('skip');

        $handler->handle($register);
    }

    /**
     * @param array<string, mixed> $value
     */
    #[DataProvider('falseWhenProvider')]
    public function testHandleCallsSkipAfterPassWhenWhenIsFalse(mixed $value): void
    {
        $register = $this->createMock(Register::class);
        $handler = new WhenHandler();

        $register->expects($this->once())
            ->method('pass')
            ->id('pass');

        $register->expects($this->once())
            ->method('get')
            ->with('when')
            ->willReturn($value);

        $register->expects($this->once())
            ->method('skip')
            ->after('pass');

        $handler->handle($register);
    }

    /**
     * @param array<string, mixed> $value
     */
    #[DataProvider('valuesWithUnsupportedTypeProvider')]
    public function testHandleThrowsExceptionWhenWhenHasUnsupportedType(mixed $value): void
    {
        $register = $this->createMock(Register::class);
        $handler = new WhenHandler();

        $register->expects($this->once())
            ->method('get')
            ->with('when')
            ->willReturn($value);

        $this->expectexception(UnsupportedWhenTypeException::class);

        $handler->handle($register);
    }

    /**
     * @return mixed[][]
     */
    public static function trueWhenProvider(): array
    {
        return [
            'boolean' => [true],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function falseWhenProvider(): array
    {
        return [
            'boolean' => [false],
            // 'null' => [null],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function valuesWithUnsupportedTypeProvider(): array
    {
        return [
            'array' => [[]],
            'float' => [1.234],
            'integer' => [1234],
            'string' => ['true'],
        ];
    }
}
