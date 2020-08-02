<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\FileSystem;

use LizardsAndPumpkins\Import\FileStorage\FileStorageReader;
use LizardsAndPumpkins\Util\FileSystem\Exception\FileNotReadableException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageReader
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystem
 */
class LocalFilesystemStorageReaderTest extends TestCase
{
    /**
     * @var LocalFilesystemStorageReader
     */
    private $reader;

    /**
     * @var string
     */
    private $testBaseDirPath;

    final protected function setUp(): void
    {
        $this->testBaseDirPath = sys_get_temp_dir() . '/lizards-and-pumpkins-local-filesystem-storage';
        mkdir($this->testBaseDirPath);

        $this->reader = new LocalFilesystemStorageReader();
    }

    final protected function tearDown(): void
    {
        if (is_dir($this->testBaseDirPath) && is_writable($this->testBaseDirPath)) {
            (new LocalFilesystem())->removeDirectoryAndItsContent($this->testBaseDirPath);
        }
    }

    public function testFileStorageReaderInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(FileStorageReader::class, $this->reader);
    }

    public function testExceptionIsThrownIfFileIsNotReadable(): void
    {
        $this->expectException(FileNotReadableException::class);
        $this->reader->getFileContents('/some-non-existing-file');
    }

    public function testFileContentsIsReturned(): void
    {
        $filePath = $this->testBaseDirPath . '/foo';
        $content = 'bar';

        file_put_contents($filePath, $content);

        $result = $this->reader->getFileContents($filePath);

        $this->assertEquals($content, $result);
    }
}
