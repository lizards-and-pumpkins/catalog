<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Utils\FileNotWritableException;
use LizardsAndPumpkins\Utils\LocalFilesystem;

/**
 * @covers \LizardsAndPumpkins\LocalFilesystemStorageWriter
 * @uses   \LizardsAndPumpkins\Utils\LocalFilesystem
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

        $this->writer = new LocalFilesystemStorageWriter($this->testBaseDirPath);
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
        $this->setExpectedException(FileNotWritableException::class);
        chmod($this->testBaseDirPath, 0000);
        $this->writer->putFileContents('foo', 'bar');
    }

    public function testFileContentsIsWritten()
    {
        $fileName = 'foo';
        $content = 'bar';

        $this->writer->putFileContents($fileName, $content);

        $actualContent = file_get_contents($this->testBaseDirPath . '/' . $fileName);

        $this->assertEquals($content, $actualContent);
    }
}
