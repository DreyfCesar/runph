<?php

declare(strict_types=1);

namespace Tests\Unit\Playbook;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Runph\Playbook\Exceptions\InvalidPlaybookException;
use Runph\Playbook\PlaybookConverter;
use Runph\Services\Filesystem\Exceptions\FileNotFoundException;
use Runph\Services\Filesystem\Exceptions\FileNotReadableException;
use Runph\Services\Filesystem\Filesystem;
use Runph\Services\Yaml\YamlHandler;
use Throwable;

class PlaybookConverterTest extends TestCase
{
    private PlaybookConverter $converter;

    /** @var MockObject&Filesystem */
    private Filesystem $filesystem;

    /** @var MockObject&YamlHandler */
    private YamlHandler $yamlHandler;

    public function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->yamlHandler = $this->createMock(YamlHandler::class);
        $this->converter = new PlaybookConverter($this->filesystem, $this->yamlHandler);
    }

    public function testItThrowsWhenFileDoesNotExist(): void
    {
        $file = 'unexistent_file';

        $this->mockNotReadableFile($file, new FileNotFoundException($file));
        $this->expectException(FileNotFoundException::class);
        $this->converter->toArray($file);
    }

    public function testItThrowsWhenFileIsNotReadable(): void
    {
        $file = 'unreadable_file';

        $this->mockNotReadableFile($file, new FileNotReadableException($file));
        $this->expectException(FileNotReadableException::class);
        $this->converter->toArray($file);
    }

    public function testItThrowsWhenYamlDoesNotParseToArray(): void
    {
        $file = 'invalid.yaml';
        $convertion = 'any';

        $this->mockExistentAndReadableFile($file);
        $this->mockYamlConversion($file, $convertion);
        $this->expectException(InvalidPlaybookException::class);
        $this->converter->toArray($file);
    }

    public function testItConvertsYamlToArraySuccessfully(): void
    {
        $file = 'file.yaml';
        $parsed = ['name' => 'runph'];

        $this->mockExistentAndReadableFile($file);
        $this->mockYamlConversion($file, $parsed);

        $resultOfConvertion = $this->converter->toArray($file);

        $this->assertSame($parsed, $resultOfConvertion);
    }

    private function mockNotReadableFile(string $file, Throwable $exception): void
    {
        $this->filesystem
            ->expects($this->once())
            ->method('ensureReadable')
            ->with($file)
            ->willThrowException($exception);
    }

    private function mockExistentAndReadableFile(string $file): void
    {
        $this->filesystem
            ->expects($this->once())
            ->method('ensureReadable')
            ->with($file);
    }

    private function mockYamlConversion(string $file, mixed $convertion): void
    {
        $this->yamlHandler
            ->expects($this->once())
            ->method('parseFile')
            ->with($file)
            ->willReturn($convertion);
    }
}
