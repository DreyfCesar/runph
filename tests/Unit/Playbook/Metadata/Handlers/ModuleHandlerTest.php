<?php

declare(strict_types=1);

namespace Tests\Unit\Playbook\Metadata\Handlers;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Runph\Playbook\Exceptions\MissingModuleException;
use Runph\Playbook\Exceptions\MultipleModuleInTaskException;
use Runph\Playbook\Metadata\Handlers\ModuleHandler;
use Runph\Playbook\Metadata\Register;
use Runph\Playbook\ModuleRunner;
use Runph\Services\Config\ConfigLoader;

class ModuleHandlerTest extends TestCase
{
    /** @var MockObject&ConfigLoader<string, mixed> */
    private ConfigLoader $configLoader;

    /** @var MockObject&ModuleRunner */
    private ModuleRunner $moduleRunner;

    /** @var MockObject&Register */
    private Register $register;

    public function setUp(): void
    {
        $this->register = $this->createMock(Register::class);
        $this->configLoader = $this->createMock(ConfigLoader::class);
        $this->moduleRunner = $this->createMock(ModuleRunner::class);
    }

    public function testHandleDelegatesSingleModuleTaskToModuleRunner(): void
    {
        $enabledModules = ['fake_module' => 'Fake\\Module'];
        $tasksModules = ['fake_module' => []];

        $this->mockConfigLoader(
            enabledModules: $enabledModules,
            metadata: ['name' => 'Handlers\\NameHandler', 'foo' => 'Handlers\\FooHandler']
        );

        $this->givenRegisterData($tasksModules + [
            'name' => 'Playing fake module',
            'foo' => 'bar',
        ]);

        $this->moduleRunner
            ->expects($this->once())
            ->method('run')
            ->with($tasksModules, $enabledModules);

        $this->createHandler()->handle($this->register);
    }

    public function testHandleThrowsExceptionWhenNoModuleIsDefined(): void
    {
        $this->mockConfigLoader(
            enabledModules: ['fake_module' => 'Fake\\Module'],
            metadata: ['name' => 'Handlers\\NameHandler', 'foo' => 'Handlers\\FooHandler']
        );

        $this->givenRegisterData([
            'name' => 'Playing fake module',
            'foo' => 'bar',
        ]);

        $this->neverRunModule();
        $this->expectException(MissingModuleException::class);
        $this->createHandler()->handle($this->register);
    }

    public function testHandleThrowsExceptionWhenMultipleModulesAreDefined(): void
    {
        $this->mockConfigLoader(
            enabledModules: ['fake_module' => 'Fake\\Module'],
            metadata: ['name' => 'Handlers\\NameHandler', 'foo' => 'Handlers\\FooHandler'],
        );

        $this->givenRegisterData([
            'fake_module' => [], 'other_module' => [],
            'name' => 'Playing fake module',
            'foo' => 'bar',
        ]);

        $this->neverRunModule();
        $this->expectException(MultipleModuleInTaskException::class);
        $this->createHandler()->handle($this->register);
    }

    /**
     * @param array<string, string> $enabledModules
     * @param array<string, string> $metadata
     */
    private function mockConfigLoader(array $enabledModules, array $metadata): void
    {
        $this->configLoader
            ->method('load')
            ->willReturnCallback(function (string $file) use ($enabledModules, $metadata) {
                return match ($file) {
                    'meta_handlers' => $metadata,
                    'tasks' => $enabledModules,
                    default => [],
                };
            });
    }

    /**
     * @param array<string, mixed> $data
     */
    private function givenRegisterData(array $data): void
    {
        $this->register
            ->expects($this->once())
            ->method('data')
            ->willReturn($data);
    }

    private function createHandler(): ModuleHandler
    {
        return new ModuleHandler($this->configLoader, $this->moduleRunner);
    }

    private function neverRunModule(): void
    {
        $this->moduleRunner
            ->expects($this->never())
            ->method('run');
    }
}
