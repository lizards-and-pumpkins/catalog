<?php

namespace Brera;

/**
 * @covers \Brera\LocalFilesystemStorage
 */
class LocalFilesystemStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocalFilesystemStorage
     */
    private $file;

    /**
     * @var string
     */
    private $testFilePath;

    protected function setUp()
    {
        $this->file = new LocalFilesystemStorage();

        $this->testFilePath = sys_get_temp_dir() . '/test-file.brera';
        file_put_contents($this->testFilePath, 'foo');
    }

    protected function tearDown()
    {
        if (file_exists($this->testFilePath) && is_writable($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    /**
     * @test
     */
    public function itShouldImplementStaticFileInterface()
    {
        $this->assertInstanceOf(StaticFileStorage::class, $this->file);
    }

    /**
     * @test
     */
    public function itShouldReturnFileContents()
    {
        $result = $this->file->getFileContents($this->testFilePath);
        $expectedContent = file_get_contents($this->testFilePath);

        $this->assertEquals($expectedContent, $result);
    }

    /**
     * @test
     */
    public function itShouldWriteFileContents()
    {
        file_put_contents($this->testFilePath, '');

        $content = 'bar';
        $this->file->putFileContents($this->testFilePath, $content);
        $actualContent = file_get_contents($this->testFilePath);

        $this->assertEquals($content, $actualContent);
    }
}
