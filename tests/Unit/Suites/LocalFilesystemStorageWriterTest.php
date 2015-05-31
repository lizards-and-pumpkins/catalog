<?php

namespace Brera;

use Brera\Utils\LocalFilesystem;

/**
 * @covers \Brera\LocalFilesystemStorageWriter
 * @uses   \Brera\Utils\LocalFilesystem
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
        $this->testBaseDirPath = sys_get_temp_dir() . '/brera-result-image';
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

    /**
     * @test
     */
    public function itShouldImplementStaticFileInterface()
    {
        $this->assertInstanceOf(FileStorageWriter::class, $this->writer);
    }

    /**
     * @test
     * @expectedException \Brera\Utils\FileNotWritableException
     */
    public function itShouldThrownAnExceptionIfDestinationIsNotWritable()
    {
        chmod($this->testBaseDirPath, 0000);
        $this->writer->putFileContents('foo', 'bar');
    }

    /**
     * @test
     */
    public function itShouldWriteFileContents()
    {
        $fileName = 'foo';
        $content = 'bar';

        $this->writer->putFileContents($fileName, $content);

        $actualContent = file_get_contents($this->testBaseDirPath . '/' . $fileName);

        $this->assertEquals($content, $actualContent);
    }
}
