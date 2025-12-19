<?php

declare(strict_types=1);

namespace Tests\Unit\Playbook\Metadata;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Runph\Playbook\Exceptions\InvalidHandlerException;
use Runph\Playbook\Metadata\HandlerInterface;
use Runph\Playbook\Metadata\MetaHandler;
use Runph\Playbook\Metadata\Register;
use Runph\Services\Config\ConfigLoader;
use stdClass;

class MetaHandlerTest extends TestCase
{
    /** @var MockObject&ConfigLoader<string, class-string<HandlerInterface>> */
    private ConfigLoader $configLoader;

    /** @var MockObject&ContainerInterface */
    private ContainerInterface $container;

    /** @var MockObject&HandlerInterface */
    private HandlerInterface $mockHandler;

    public function setUp(): void
    {
        parent::setUp();

        $this->configLoader = $this->createMock(ConfigLoader::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->mockHandler = $this->createMock(HandlerInterface::class);
    }

    public function testRunExecutesTheHandlersFromContainer(): void
    {
        /** @var class-string<mixed>[] */
        $handlers = [
            'Handlers\\HandlerClassname',
        ];

        $this->mockConfigLoader($handlers);

        $this->container->expects($this->exactly(count($handlers)))
            ->method('get')
            ->willReturnCallback(function (string $id) use ($handlers) {
                $this->assertContains($id, $handlers);
                return $this->mockHandler;
            });

        $register = $this->createMock(Register::class);

        $this->mockHandler->expects($this->exactly(count($handlers)))
            ->method('handle')
            ->with($register);

        $this->createMetaHandler()->run($register);
    }

    public function testRunExecutesTheHandlersInOrder(): void
    {
        /** @var class-string<mixed>[] */
        $handlers = [
            'Handlers\\FirstHandler',
            'Handlers\\SecondHandler',
            'Handlers\\ThirdHandler',
        ];

        $firstHandler = $this->createMock(HandlerInterface::class);
        $secondHandler = $this->createMock(HandlerInterface::class);
        $thirdHandler = $this->createMock(HandlerInterface::class);

        $this->mockConfigLoader($handlers);

        $this->container->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap([
                ['Handlers\\FirstHandler', $firstHandler],
                ['Handlers\\SecondHandler', $secondHandler],
                ['Handlers\\ThirdHandler', $thirdHandler],
            ]);

        $executionOrder = [];
        $register = $this->createMock(Register::class);
        $register->method('shouldSkip')->willReturn(false);

        $firstHandler->expects($this->once())
            ->method('handle')
            ->with($register)
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'first';
            });

        $secondHandler->expects($this->once())
            ->method('handle')
            ->with($register)
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'second';
            });

        $thirdHandler->expects($this->once())
            ->method('handle')
            ->with($register)
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'third';
            });

        $this->createMetaHandler()->run($register);
        $this->assertSame(['first', 'second', 'third'], $executionOrder);
    }

    public function testRunStopsWhenRegisterShouldSkip(): void
    {
        /** @var class-string<mixed>[] */
        $handlers = [
            'Handlers\\FirstHandler',
            'Handlers\\SecondHandler',
            'Handlers\\ThirdHandler',
        ];

        $firstHandler = $this->createMock(HandlerInterface::class);
        $secondHandler = $this->createMock(HandlerInterface::class);
        $thirdHandler = $this->createMock(HandlerInterface::class);

        $this->mockConfigLoader($handlers);

        $this->container->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap([
                ['Handlers\\FirstHandler', $firstHandler],
                ['Handlers\\SecondHandler', $secondHandler],
                ['Handlers\\ThirdHandler', $thirdHandler],
            ]);

        $register = $this->createMock(Register::class);

        $register->expects($this->exactly(2))
            ->method('shouldSkip')
            ->willReturnOnConsecutiveCalls(false, true);

        $firstHandler->expects($this->once())
            ->method('handle')
            ->with($register);

        $secondHandler->expects($this->never())
            ->method('handle');

        $thirdHandler->expects($this->never())
            ->method('handle');

        $this->createMetaHandler()->run($register);
    }

    #[DataProvider('nonHandlerValuesProvider')]
    public function testMetaHandlerThrowsExceptionWhenContainerReturnsNonHandler(mixed $nonHandlerValue): void
    {
        // @phpstan-ignore-next-line
        $this->mockConfigLoader(['SomeHandlerClass']);

        $this->container
            ->method('get')
            ->with('SomeHandlerClass')
            ->willReturn($nonHandlerValue);

        $this->expectException(InvalidHandlerException::class);
        $this->createMetaHandler();
    }

    /**
     * @return mixed[][]
     */
    public static function nonHandlerValuesProvider(): array
    {
        return [
            'stdClass object' => [new stdClass()],
            'plain object' => [new class () {}],
            'string' => ['not a handler'],
            'integer' => [42],
            'float' => [3.14],
            'boolean' => [true],
            'array' => [['data']],
            'null' => [null],
            'callable' => [fn () => 'callable'],
        ];
    }

    /**
     * @param class-string<mixed>[] $handlers
     */
    private function mockConfigLoader(array $handlers): void
    {
        $this->configLoader->expects($this->once())
            ->method('load')
            ->with('meta_handlers')
            ->willReturn($handlers);
    }

    private function createMetaHandler(): MetaHandler
    {
        return new MetaHandler($this->configLoader, $this->container);
    }
}
