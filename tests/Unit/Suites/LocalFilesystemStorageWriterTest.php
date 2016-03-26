<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Import\FileStorage\FileStorageWriter;
use LizardsAndPumpkins\Util\FileSystem\Exception\FileNotWritableException;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystem;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageWriter;

/**
 * @covers \LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageWriter
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystem
 */
class LocalFilesystemStorageWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocalFilesystemStorageWriter
     */
    private $writer;

    /**
     * @var string
     */
    private $testBaseDirPath;

    protected function setUp()
    {
        $this->testBaseDirPath = sys_get_temp_dir() . '/lizards-and-pumpkins-result-image';
        mkdir($this->testBaseDirPath);

        $this->writer = new LocalFilesystemStorageWriter();
    }

    protected function tearDown()
    {
        chmod($this->testBaseDirPath, 0777);

        if (is_dir($this->testBaseDirPath) && is_writable($this->testBaseDirPath)) {
            (new LocalFilesystem())->removeDirectoryAndItsContent($this->testBaseDirPath);
        }
    }

    public function testStaticFileInterfaceIsImplemented()
    {
        $this->assertInstanceOf(FileStorageWriter::class, $this->writer);
    }

    public function testExceptionIsThrownIfDestinationIsNotWritable()
    {
        $this->expectException(FileNotWritableException::class);
        chmod($this->testBaseDirPath, 0000);
        $this->writer->putFileContents($this->testBaseDirPath . '/foo', 'bar');
    }

    public function testFileContentsIsWritten()
    {
        $filePath = $this->testBaseDirPath . '/foo';
        $content = 'bar';

        $this->writer->putFileContents($filePath, $content);

        $actualContent = file_get_contents($filePath);

        $this->assertEquals($content, $actualContent);
    }
}
