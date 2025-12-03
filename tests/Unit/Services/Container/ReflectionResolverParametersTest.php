<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Container;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Runph\Services\Container\Exceptions\UnresolvableDependencyException;
use Runph\Services\Container\ReflectionResolver;

class ReflectionResolverParametersTest extends TestCase
{
    /** @var MockObject&ContainerInterface */
    private ContainerInterface $container;
    private ReflectionResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);
        $this->resolver = new ReflectionResolver();
        $this->resolver->setContainer($this->container);
    }

    /**
     * @param class-string $class
     * @param array<string, mixed> $params
     */
    #[DataProvider('validParameterProvider')]
    public function testItResolvesProvidedParameters(string $class, array $params, mixed $expectedValue): void
    {
        $instance = $this->resolver->get($class, $params);

        // @phpstan-ignore-next-line
        $this->assertSame($expectedValue, $instance->value);
    }

    /**
     * @return array<string, mixed[]>
     */
    public static function validParameterProvider(): array
    {
        return [
            'simplestring' => [
                DummyParamString::class,
                ['value' => 'hello'],
                'hello',
            ],
            'int param' => [
                DummyParamInt::class,
                ['value' => 42],
                42,
            ],
            'union type - matches string' => [
                DummyParamUnionStringInt::class,
                ['value' => 'abc'],
                'abc',
            ],
            'union type - matches int' => [
                DummyParamUnionStringInt::class,
                ['value' => 999],
                999,
            ],
            'mixed type' => [
                DummyParamMixed::class,
                ['value' => ['any', 'data']],
                ['any', 'data'],
            ],
        ];
    }

    /**
     * @param class-string $class
     * @param array<string, mixed> $params
     */
    #[DataProvider('invalidParameterProvider')]
    public function testItThrowsWhenProvidedParameterDoesNotMatchType(string $class, array $params): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->resolver->get($class, $params);
    }

    /**
     * @return array<string, mixed[]>
     */
    public static function invalidParameterProvider(): array
    {
        return [
            'expects string, given int' => [
                DummyParamString::class,
                ['value' => 100],
            ],
            'expects int, given string' => [
                DummyParamInt::class,
                ['value' => 'not an int'],
            ],
            'union type - no match' => [
                DummyParamUnionStringInt::class,
                ['value' => 10.5], // float no válido
            ],
        ];
    }
}

class DummyParamString
{
    public function __construct(public string $value) {}
}

class DummyParamInt
{
    public function __construct(public int $value) {}
}

class DummyParamUnionStringInt
{
    public function __construct(public string|int $value) {}
}

class DummyParamMixed
{
    public function __construct(public mixed $value) {}
}
