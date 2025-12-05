<?php

declare(strict_types=1);

namespace Tests\Unit\Playbook;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Playbook\Exceptions\ModulesNotFoundException;
use Runph\Playbook\ModuleRunner;
use Runph\Services\Container\Contracts\FactoryContainerInterface;

class ModuleRunnerTest extends TestCase
{
    /** @var MockObject&FactoryContainerInterface */
    private FactoryContainerInterface $factory;

    private ModuleRunner $moduleRunner;

    public function setUp(): void
    {
        parent::setUp();

        $this->factory = $this->createMock(FactoryContainerInterface::class);
        $this->moduleRunner = new ModuleRunner($this->factory);
    }

    public function testItThrowsExceptionWhenModulesAreNotFound(): void
    {
        $definition = ['aguacate' => []];
        $modules = ['palta' => ModuleInterface::class];

        $this->expectException(ModulesNotFoundException::class);

        $this->moduleRunner->run($definition, $modules);
    }

    public function testItBuildsTheModulesAndRunsThem(): void
    {
        $mockModule = $this->createMock(ModuleInterface::class);
        $mockModule->expects($this->once())->method('run');

        $params = ['foo' => 'bar'];
        $expectedParams = ['value' => $params] + $params;

        $this->factory
            ->expects($this->once())
            ->method('make')
            ->with(ModuleInterface::class, $expectedParams)
            ->willReturn($mockModule);

        $definition = ['fakemodule' => $params];
        $modules = ['fakemodule' => ModuleInterface::class];

        $this->moduleRunner->run($definition, $modules);
    }

    public function testItDoesNothingIfDefinitionIsEmpty(): void
    {
        $definition = [];
        $modules = ['foo' => ModuleInterface::class];

        $this->factory->expects($this->never())->method('make');

        $this->moduleRunner->run($definition, $modules);
    }

    public function testItRunsMultipleModules(): void
    {
        $mockModule1 = $this->createMock(ModuleInterface::class);
        $mockModule2 = $this->createMock(ModuleInterface::class);

        $mockModule1->expects($this->once())->method('run');
        $mockModule2->expects($this->once())->method('run');

        $this->factory
            ->expects($this->exactly(2))
            ->method('make')
            ->willReturnOnConsecutiveCalls($mockModule1, $mockModule2);

        $definition = [
            'module1' => ['a' => 1],
            'module2' => ['b' => 2],
        ];
        $modules = [
            'module1' => ModuleInterface::class,
            'module2' => ModuleInterface::class,
        ];

        $this->moduleRunner->run($definition, $modules);
    }

    public function testItAddsValueKeyEvenForEmptyArray(): void
    {
        $mockModule = $this->createMock(ModuleInterface::class);
        $mockModule->expects($this->once())->method('run');

        $this->factory
            ->expects($this->once())
            ->method('make')
            ->with(ModuleInterface::class, ['value' => []])
            ->willReturn($mockModule);

        $definition = ['fakemodule' => []];
        $modules = ['fakemodule' => ModuleInterface::class];

        $this->moduleRunner->run($definition, $modules);
    }

    public function testExceptionMessageContainsAllMissingModules(): void
    {
        $definition = [
            'a' => [],
            'b' => [],
            'c' => [],
        ];

        $modules = [
            'a' => ModuleInterface::class,
        ];

        $missingModules = array_keys(array_diff_key($definition, $modules));

        $this->expectException(ModulesNotFoundException::class);
        $this->expectExceptionMessageMatches('/(' . implode('|', array_map('preg_quote', $missingModules)) . ')/');

        $this->moduleRunner->run($definition, $modules);
    }

    #[DataProvider('scalarValuesProvider')]
    public function testItWrapsScalarValuesInParameters(mixed $scalar): void
    {
        $mockModule = $this->createMock(ModuleInterface::class);
        $mockModule->expects($this->once())->method('run');

        $this->factory
            ->expects($this->once())
            ->method('make')
            ->willReturnCallback(function (string $class, array $params) use ($scalar, $mockModule) {
                self::assertEquals(['value' => $scalar], $params);
                return $mockModule;
            });

        $definition = ['fakemodule' => $scalar];
        $modules = ['fakemodule' => ModuleInterface::class];

        $this->moduleRunner->run($definition, $modules);
    }

    /**
     * @return array<string, mixed[]>
     */
    public static function scalarValuesProvider(): array
    {
        return [
            'int value' => [42],
            'string value' => ['hello'],
            'float value' => [3.14],
            'bool true' => [true],
            'bool false' => [false],
            'null value' => [null],
        ];
    }
}
