<?php

namespace Brera;

use Brera\Utils\LocalFilesystem;

/**
 * @covers \Brera\LocalFilesystemStorageReader
 * @uses \Brera\Utils\LocalFilesystem
 */
class LocalFilesystemStorageReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocalFilesystemStorageReader
     */
    private $reader;

    /**
     * @var string
     */
    private $testBaseDirPath;

    protected function setUp()
    {
        $this->testBaseDirPath = sys_get_temp_dir() . '/brera-local-filesystem-storage';
        mkdir($this->testBaseDirPath);

        $this->reader = new LocalFilesystemStorageReader($this->testBaseDirPath);
    }

    protected function tearDown()
    {
        if (is_dir($this->testBaseDirPath) && is_writable($this->testBaseDirPath)) {
            (new LocalFilesystem())->removeDirectoryAndItsContent($this->testBaseDirPath);
        }
    }

    /**
     * @test
     */
    public function itShouldImplementFileStorageReaderInterface()
    {
        $this->assertInstanceOf(FileStorageReader::class, $this->reader);
    }

    /**
     * @test
     * @expectedException \Brera\Utils\FileNotReadableException
     */
    public function itShouldThrowAnExceptionIfFileIsNotReadable()
    {
        $this->reader->getFileContents('/some-non-existing-file');
    }

    /**
     * @test
     */
    public function itShouldReturnFileContents()
    {
        $fileName = 'foo';
        $content = 'bar';

        file_put_contents($this->testBaseDirPath . '/' . $fileName, $content);

        $result = $this->reader->getFileContents($fileName);

        $this->assertEquals($content, $result);
    }
}
