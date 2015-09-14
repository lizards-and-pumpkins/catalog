<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Utils\FileNotReadableException;
use LizardsAndPumpkins\Utils\LocalFilesystem;

/**
 * @covers \LizardsAndPumpkins\LocalFilesystemStorageReader
 * @uses \LizardsAndPumpkins\Utils\LocalFilesystem
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
        $this->testBaseDirPath = sys_get_temp_dir() . '/lizards-and-pumpkins-local-filesystem-storage';
        mkdir($this->testBaseDirPath);

        $this->reader = new LocalFilesystemStorageReader($this->testBaseDirPath);
    }

    protected function tearDown()
    {
        if (is_dir($this->testBaseDirPath) && is_writable($this->testBaseDirPath)) {
            (new LocalFilesystem())->removeDirectoryAndItsContent($this->testBaseDirPath);
        }
    }

    public function testFileStorageReaderInterfaceIsImplemented()
    {
        $this->assertInstanceOf(FileStorageReader::class, $this->reader);
    }

    public function testExceptionIsThrownIfFileIsNotReadable()
    {
        $this->setExpectedException(FileNotReadableException::class);
        $this->reader->getFileContents('/some-non-existing-file');
    }

    public function testFileContentsIsReturned()
    {
        $fileName = 'foo';
        $content = 'bar';

        file_put_contents($this->testBaseDirPath . '/' . $fileName, $content);

        $result = $this->reader->getFileContents($fileName);

        $this->assertEquals($content, $result);
    }
}
