<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Config;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Runph\Services\Config\ConfigLoader;
use Runph\Services\Config\Exceptions\InvalidConfigFileException;
use Runph\Services\Filesystem\Filesystem;

class ConfigLoaderTest extends TestCase
{
    /** @var MockObject&Filesystem */
    private Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();
        $this->filesystem = $this->createMock(Filesystem::class);
    }

    public function testItLoadsAConfigFileSuccessfully(): void
    {
        $loader = new ConfigLoader($this->filesystem, '/config');

        $this->filesystem
            ->method('requireFile')
            ->with('/config/app.php')
            ->willReturn(['debug' => true]);

        $result = $loader->load('app');

        $this->assertSame(['debug' => true], $result);
    }

    public function testItAppendsPhpExtensionIfMissing(): void
    {
        $loader = new ConfigLoader($this->filesystem, '/config');

        $this->filesystem
            ->method('requireFile')
            ->with('/config/db.php')
            ->willReturn(['driver' => 'mysql']);

        $result = $loader->load('db');

        $this->assertSame(['driver' => 'mysql'], $result);
    }

    public function testItThrowsIfConfigFileDoesNotReturnArray(): void
    {
        $loader = new ConfigLoader($this->filesystem, '/config');

        $this->filesystem
            ->method('requireFile')
            ->with('/config/app.php')
            ->willReturn('invalid');

        $this->expectException(InvalidConfigFileException::class);

        $loader->load('app');
    }
}
