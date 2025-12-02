<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Container;

use PHPUnit\Framework\TestCase;
use Runph\Services\Container\Container;
use stdClass;

class ContainerTest extends TestCase
{
    public function testSetStoresAndReturnsService(): void
    {
        $container = new Container();
        $service = new stdClass();

        $result = $container->set(stdClass::class, $service);

        $this->assertSame($service, $result);
    }

    public function testHasReturnsTrueWhenServiceExists(): void
    {
        $container = new Container();
        $container->set('foo', 123);

        $this->assertTrue($container->has('foo'));
    }

    public function testHasReturnsFalseWhenServiceDoesNotExist(): void
    {
        $container = new Container();
        $this->assertFalse($container->has('undefined'));
    }

    public function testGetReturnsPreviouslyStoredService(): void
    {
        $container = new Container();
        $service = new stdClass();

        $container->set(stdClass::class, $service);
        $result = $container->get(stdClass::class);

        $this->assertSame($service, $result);
    }

    public function testGetBuildsNewServiceViaReflection(): void
    {
        $container = new Container();
        $result = $container->get(stdClass::class);

        $this->assertInstanceOf(stdClass::class, $result);
    }

    public function testGetCachesResolvedServices(): void
    {
        $container = new Container();

        $first = $container->get(stdClass::class);
        $second = $container->get(stdClass::class);

        $this->assertSame($first, $second);
    }
}
