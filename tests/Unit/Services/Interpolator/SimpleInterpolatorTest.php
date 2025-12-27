<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Interpolator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Runph\Services\Interpolator\SimpleInterpolator;
use Runph\Services\Memory\Contracts\MemoryInterface;
use RuntimeException;

class SimpleInterpolatorTest extends TestCase
{
    /** @var MockObject&MemoryInterface */
    private MemoryInterface $memory;

    private SimpleInterpolator $interpolator;

    protected function setUp(): void
    {
        $this->memory = $this->createMock(MemoryInterface::class);
        $this->interpolator = new SimpleInterpolator($this->memory);
    }

    public function testInterpolatesSingleVariableWithBraces(): void
    {
        $this->memory
            ->method('has')
            ->with('name')
            ->willReturn(true);

        $this->memory
            ->method('get')
            ->with('name')
            ->willReturn('John');

        $result = $this->interpolator->interpolate('Hello ${name}!');

        $this->assertSame('Hello John!', $result);
    }

    public function testInterpolatesSingleVariableWithoutBraces(): void
    {
        $this->memory
            ->method('has')
            ->with('name')
            ->willReturn(true);

        $this->memory
            ->method('get')
            ->with('name')
            ->willReturn('Jane');

        $result = $this->interpolator->interpolate('Hello $name!');

        $this->assertSame('Hello Jane!', $result);
    }

    public function testInterpolatesMultipleVariables(): void
    {
        $this->memory
            ->method('has')
            ->willReturnCallback(fn (string $key) => in_array($key, ['first', 'last'], true));

        $this->memory
            ->method('get')
            ->willReturnCallback(fn (string $key) => match($key) {
                'first' => 'John',
                'last' => 'Doe',
                default => null,
            });

        $result = $this->interpolator->interpolate('Name: ${first} $last');

        $this->assertSame('Name: John Doe', $result);
    }

    public function testInterpolatesNumericValues(): void
    {
        $this->memory
            ->method('has')
            ->with('age')
            ->willReturn(true);

        $this->memory
            ->method('get')
            ->with('age')
            ->willReturn(42);

        $result = $this->interpolator->interpolate('Age: ${age}');

        $this->assertSame('Age: 42', $result);
    }

    public function testLeavesPlaceholderWhenVariableNotFound(): void
    {
        $this->memory
            ->method('has')
            ->with('unknown')
            ->willReturn(false);

        $result = $this->interpolator->interpolate('Hello ${unknown}!');

        $this->assertSame('Hello ${unknown}!', $result);
    }

    public function testHandlesEscapedVariablesWithBraces(): void
    {
        $result = $this->interpolator->interpolate('Escaped: \${name}');

        $this->assertSame('Escaped: ${name}', $result);
    }

    public function testHandlesEscapedVariablesWithoutBraces(): void
    {
        $result = $this->interpolator->interpolate('Escaped: \$name');

        $this->assertSame('Escaped: $name', $result);
    }

    public function testHandlesMixedEscapedAndNonEscapedVariables(): void
    {
        $this->memory
            ->method('has')
            ->with('real')
            ->willReturn(true);

        $this->memory
            ->method('get')
            ->with('real')
            ->willReturn('value');

        $result = $this->interpolator->interpolate('Real: ${real}, Escaped: \${fake}');

        $this->assertSame('Real: value, Escaped: ${fake}', $result);
    }

    public function testThrowsExceptionWhenVariableIsNotStringOrNumeric(): void
    {
        $this->memory
            ->method('has')
            ->with('invalid')
            ->willReturn(true);

        $this->memory
            ->method('get')
            ->with('invalid')
            ->willReturn(['array']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Variable "invalid" must be string or numeric, array given');

        $this->interpolator->interpolate('Value: ${invalid}');
    }

    public function testThrowsExceptionWhenVariableIsObject(): void
    {
        $this->memory
            ->method('has')
            ->with('obj')
            ->willReturn(true);

        $this->memory
            ->method('get')
            ->with('obj')
            ->willReturn(new \stdClass());

        $this->expectException(RuntimeException::class);

        $this->interpolator->interpolate('Value: ${obj}');
    }

    public function testThrowsExceptionWhenVariableIsNull(): void
    {
        $this->memory
            ->method('has')
            ->with('nullable')
            ->willReturn(true);

        $this->memory
            ->method('get')
            ->with('nullable')
            ->willReturn(null);

        $this->expectException(RuntimeException::class);

        $this->interpolator->interpolate('Value: ${nullable}');
    }

    public function testHandlesEmptyString(): void
    {
        $result = $this->interpolator->interpolate('');

        $this->assertSame('', $result);
    }

    public function testHandlesStringWithoutVariables(): void
    {
        $result = $this->interpolator->interpolate('Just a plain string');

        $this->assertSame('Just a plain string', $result);
    }

    public function testHandlesConsecutiveVariables(): void
    {
        $this->memory
            ->method('has')
            ->willReturnCallback(fn (string $key) => in_array($key, ['a', 'b'], true));

        $this->memory
            ->method('get')
            ->willReturnCallback(fn (string $key) => match($key) {
                'a' => 'A',
                'b' => 'B',
                default => null,
            });

        $result = $this->interpolator->interpolate('${a}${b}');

        $this->assertSame('AB', $result);
    }

    public function testHandlesVariableAtStartOfString(): void
    {
        $this->memory
            ->method('has')
            ->with('start')
            ->willReturn(true);

        $this->memory
            ->method('get')
            ->with('start')
            ->willReturn('Beginning');

        $result = $this->interpolator->interpolate('${start} of string');

        $this->assertSame('Beginning of string', $result);
    }

    public function testHandlesVariableAtEndOfString(): void
    {
        $this->memory
            ->method('has')
            ->with('end')
            ->willReturn(true);

        $this->memory
            ->method('get')
            ->with('end')
            ->willReturn('End');

        $result = $this->interpolator->interpolate('String at ${end}');

        $this->assertSame('String at End', $result);
    }

    /**
     * @param mixed[] $variables
     */
    #[DataProvider('complexInterpolationCasesProvider')]
    public function testComplexInterpolationCases(string $input, array $variables, string $expected): void
    {
        $this->memory
            ->method('has')
            ->willReturnCallback(fn (string $key) => isset($variables[$key]));

        $this->memory
            ->method('get')
            ->willReturnCallback(fn (string $key) => $variables[$key] ?? null);

        $result = $this->interpolator->interpolate($input);

        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{input: string, variables: array<string, string|int>, expected: string}>
     */
    public static function complexInterpolationCasesProvider(): array
    {
        return [
            'multiple same variable' => [
                'input' => '${name} and ${name} again',
                'variables' => ['name' => 'Bob'],
                'expected' => 'Bob and Bob again',
            ],
            'mixed braces and no braces' => [
                'input' => '${first} $last',
                'variables' => ['first' => 'John', 'last' => 'Doe'],
                'expected' => 'John Doe',
            ],
            'numbers and strings' => [
                'input' => 'Port: ${port}, Host: ${host}',
                'variables' => ['port' => 8080, 'host' => 'localhost'],
                'expected' => 'Port: 8080, Host: localhost',
            ],
            'partial variables found' => [
                'input' => '${found} and ${notfound}',
                'variables' => ['found' => 'yes'],
                'expected' => 'yes and ${notfound}',
            ],
            'only escaped variables' => [
                'input' => '\${a} \$b',
                'variables' => [],
                'expected' => '${a} $b',
            ],
        ];
    }

    public function testHandlesFloatValues(): void
    {
        $this->memory
            ->method('has')
            ->with('price')
            ->willReturn(true);

        $this->memory
            ->method('get')
            ->with('price')
            ->willReturn(19.99);

        $result = $this->interpolator->interpolate('Price: $${price}');

        $this->assertSame('Price: $19.99', $result);
    }

    public function testHandlesZeroValue(): void
    {
        $this->memory
            ->method('has')
            ->with('count')
            ->willReturn(true);

        $this->memory
            ->method('get')
            ->with('count')
            ->willReturn(0);

        $result = $this->interpolator->interpolate('Count: ${count}');

        $this->assertSame('Count: 0', $result);
    }

    public function testHandlesEmptyStringValue(): void
    {
        $this->memory
            ->method('has')
            ->with('empty')
            ->willReturn(true);

        $this->memory
            ->method('get')
            ->with('empty')
            ->willReturn('');

        $result = $this->interpolator->interpolate('Value: [${empty}]');

        $this->assertSame('Value: []', $result);
    }
}
