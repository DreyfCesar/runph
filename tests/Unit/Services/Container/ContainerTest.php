<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Container;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Runph\Services\Container\Container;
use Runph\Services\Container\ReflectionResolver;
use stdClass;

class ContainerTest extends TestCase
{
    private Container $container;

    /** @var MockObject&ReflectionResolver */
    private ReflectionResolver $reflectionResolver;

    public function setUp(): void
    {
        parent::setUp();

        $this->reflectionResolver = $this->createMock(ReflectionResolver::class);
        $this->container = new Container($this->reflectionResolver);
    }

    public function testSetStoresAndReturnsService(): void
    {
        $service = new stdClass();
        $result = $this->container->set(stdClass::class, $service);

        $this->assertSame($service, $result);
    }

    public function testHasReturnsTrueWhenServiceExists(): void
    {
        $this->container->set('foo', 123);

        $this->assertTrue($this->container->has('foo'));
    }

    public function testHasReturnsFalseWhenServiceDoesNotExist(): void
    {
        $this->assertFalse($this->container->has('undefined'));
    }

    public function testGetReturnsPreviouslyStoredService(): void
    {
        $service = new stdClass();

        $this->container->set(stdClass::class, $service);
        $result = $this->container->get(stdClass::class);

        $this->assertSame($service, $result);
    }

    public function testGetDelegatesResolutionToReflectionResolver(): void
    {
        $service = new stdClass();

        $this->reflectionResolver
            ->expects($this->once())
            ->method('get')
            ->with(stdClass::class)
            ->willReturn($service);

        $result = $this->container->get(stdClass::class);

        $this->assertSame($service, $result);
    }

    public function testMakeDoesNotCacheInstance(): void
    {
        $this->reflectionResolver
            ->method('get')
            ->with(stdClass::class, [])
            ->willReturnCallback(fn () => new stdClass());

        $instance1 = $this->container->make(stdClass::class, []);
        $instance2 = $this->container->make(stdClass::class, []);

        $this->assertInstanceOf(stdClass::class, $instance1);
        $this->assertInstanceOf(stdClass::class, $instance2);
        $this->assertNotSame($instance1, $instance2, 'make() should return a new instance each time and not cache it');
    }

    public function testSettingAlias(): void
    {
        $this->container->set(DummyInterface::class, Dummy::class);

        $this->reflectionResolver
            ->method('get')
            ->with(Dummy::class)
            ->willReturn(new Dummy());

        $result = $this->container->get(DummyInterface::class);
        $this->assertInstanceOf(Dummy::class, $result);
    }
}

interface DummyInterface {}

class Dummy implements DummyInterface {}
