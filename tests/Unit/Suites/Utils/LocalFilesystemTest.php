<?php

namespace Brera\Utils;

/**
 * @covers \Brera\Utils\LocalFilesystem
 */
class LocalFilesystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocalFilesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $testDirectoryPath;

    /**
     * @var string
     */
    private $nonWritableDirectoryPath;

    protected function setUp()
    {
        $this->filesystem = new LocalFilesystem();

        $this->testDirectoryPath = sys_get_temp_dir() . '/brera-local-filesystem-test';
        if (!is_dir($this->testDirectoryPath)) {
            mkdir($this->testDirectoryPath);
        }

        $this->nonWritableDirectoryPath = sys_get_temp_dir() . '/non-writable-directory';
        mkdir($this->nonWritableDirectoryPath);
        chmod($this->nonWritableDirectoryPath, 0000);
    }

    protected function tearDown()
    {
        $directoryIterator = new \RecursiveDirectoryIterator($this->testDirectoryPath, \FilesystemIterator::SKIP_DOTS);

        foreach (new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }

        rmdir($this->testDirectoryPath);

        chmod($this->nonWritableDirectoryPath, 0777);
        rmdir($this->nonWritableDirectoryPath);
    }

    /**
     * @test
     */
    public function itShouldRemoveDirectoryAndItsContent()
    {
        $directoryPath = $this->testDirectoryPath . '/directory-to-be-removed';

        mkdir($directoryPath);
        touch($directoryPath . '/file');
        mkdir($directoryPath . '/dir');
        symlink($directoryPath . '/file', $directoryPath . '/link');

        $this->filesystem->removeDirectoryAndItsContent($directoryPath);

        $this->assertFalse(is_dir($directoryPath));
    }

    /**
     * @test
     * @expectedException \Brera\Utils\DirectoryDoesNotExistException
     */
    public function itShouldThrowAnExceptionIfDirectoryDoesNotExist()
    {
        $this->filesystem->removeDirectoryAndItsContent('/non-existing-directory');
    }

    /**
     * @test
     * @expectedException \Brera\Utils\DirectoryNotWritableException
     */
    public function itShouldThrowAnExceptionIfDirectoryIsNotWritable()
    {
        $this->filesystem->removeDirectoryAndItsContent($this->nonWritableDirectoryPath);
    }
}
