<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Container;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Runph\Services\Container\Exceptions\ServiceClassNotFoundException;
use Runph\Services\Container\Exceptions\UnresolvableDependencyException;
use Runph\Services\Container\ReflectionResolver;

const DEFAULT_PARAM = 'default';

class ReflectionResolverTest extends TestCase
{
    /** @var MockObject&ContainerInterface */
    private ContainerInterface $container;
    private ReflectionResolver $resolver;

    public function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);
        $this->resolver = new ReflectionResolver();
        $this->resolver->setContainer($this->container);
    }

    public function testItThrowsWhenTheClassDoesNotExist(): void
    {
        $this->expectException(ServiceClassNotFoundException::class);

        // @phpstan-ignore-next-line
        $this->resolver->get('UnexistentClass');
    }

    public function testItCreatesAClassWithoutConstructor(): void
    {
        $instance = $this->resolver->get(DummyNoConstructor::class);

        $this->assertInstanceOf(DummyNoConstructor::class, $instance);
    }

    public function testItResolvesDefaultConstructorParameters(): void
    {
        $instance = $this->resolver->get(DummyWithDefaultParam::class);

        $this->assertInstanceOf(DummyWithDefaultParam::class, $instance);
        $this->assertSame(DEFAULT_PARAM, $instance->value);
    }

    public function testItResolvesDependenciesFromTheContainer(): void
    {
        $this->container
            ->method('get')
            ->with(DummyDependency::class)
            ->willReturn(new DummyDependency());

        $instance = $this->resolver->get(DummyWithDependency::class);

        $this->assertInstanceOf(DummyWithDependency::class, $instance);
        $this->assertInstanceOf(DummyDependency::class, $instance->dep);
    }

    public function testItThrowsWhenAParameterCannotBeResolved(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->resolver->Get(DummyUnresolvable::class);
    }
}

/**
 * Dummy classes for testing
 */

class DummyNoConstructor {}

class DummyWithDefaultParam
{
    public function __construct(public string $value = DEFAULT_PARAM) {}
}

class DummyDependency {}

class DummyWithDependency
{
    public function __construct(public DummyDependency $dep) {}
}

class DummyUnresolvable
{
    /**
     * @phpstan-ignore-next-line
     */
    public function __construct(string $invalidParam) {}
}
