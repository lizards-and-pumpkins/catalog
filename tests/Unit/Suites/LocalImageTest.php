<?php

namespace Brera;

use Brera\Utils\LocalFilesystem;

/**
 * @covers \Brera\LocalImage
 * @uses \Brera\Utils\LocalFilesystem
 */
class LocalImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocalImage
     */
    private $file;

    /**
     * @var string
     */
    private $testOriginalImageDir;

    /**
     * @var string
     */
    private $testResultImageDir;

    protected function setUp()
    {
        $this->testOriginalImageDir = sys_get_temp_dir() . '/brera-original-image';
        mkdir($this->testOriginalImageDir);

        $this->testResultImageDir = sys_get_temp_dir() . '/brera-result-image';
        mkdir($this->testResultImageDir);

        $this->file = new LocalImage($this->testOriginalImageDir, $this->testResultImageDir);
    }

    protected function tearDown()
    {
        $localFilesystem = new LocalFilesystem();

        if (is_dir($this->testOriginalImageDir) && is_writable($this->testOriginalImageDir)) {
            $localFilesystem->removeDirectoryAndItsContent($this->testOriginalImageDir);
        }

        if (is_dir($this->testResultImageDir) && is_writable($this->testResultImageDir)) {
            $localFilesystem->removeDirectoryAndItsContent($this->testResultImageDir);
        }
    }

    /**
     * @test
     */
    public function itShouldImplementStaticFileInterface()
    {
        $this->assertInstanceOf(FileStorage::class, $this->file);
    }

    /**
     * @test
     * @expectedException \Brera\Utils\FileNotReadableException
     */
    public function itShouldThrowAnExceptionIfFileIsNotReadable()
    {
        $this->file->getFileContents('/some-non-existing-file');
    }

    /**
     * @test
     */
    public function itShouldReturnFileContents()
    {
        $fileName = 'foo';
        $content = 'bar';

        file_put_contents($this->testOriginalImageDir . '/' . $fileName, $content);

        $result = $this->file->getFileContents($fileName);

        $this->assertEquals($content, $result);
    }

    /**
     * @test
     */
    public function itShouldWriteFileContents()
    {
        $fileName = 'foo';
        $content = 'bar';

        file_put_contents($this->testResultImageDir . '/' . $fileName, '');

        $this->file->putFileContents($fileName, $content);

        $actualContent = file_get_contents($this->testResultImageDir . '/' . $fileName);

        $this->assertEquals($content, $actualContent);
    }
}
