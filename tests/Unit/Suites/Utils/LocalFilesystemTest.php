<?php

namespace LizardsAndPumpkins\Utils;

use LizardsAndPumpkins\Utils\Exception\DirectoryDoesNotExistException;
use LizardsAndPumpkins\Utils\Exception\DirectoryNotWritableException;

/**
 * @covers \LizardsAndPumpkins\Utils\LocalFilesystem
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

        $this->testDirectoryPath = sys_get_temp_dir() . '/lizards-and-pumpkins-local-filesystem-test';
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

    public function testDirectoryAndItsContentAreRemoved()
    {
        $directoryPath = $this->testDirectoryPath . '/directory-to-be-removed';

        mkdir($directoryPath);
        touch($directoryPath . '/file');
        mkdir($directoryPath . '/dir');
        symlink($directoryPath . '/file', $directoryPath . '/link');

        $this->filesystem->removeDirectoryAndItsContent($directoryPath);

        $this->assertFalse(is_dir($directoryPath));
    }

    public function testDirectoryContentsAreRemoved()
    {
        $directoryPath = $this->testDirectoryPath . '/directory-to-be-remain';

        mkdir($directoryPath);
        touch($directoryPath . '/file-to-be-removed');
        mkdir($directoryPath . '/dir-to-be-removed');
        symlink($directoryPath . '/file-to-be-removed', $directoryPath . '/link-to-be-removed');

        $this->filesystem->removeDirectoryContents($directoryPath);

        $this->assertTrue(is_dir($directoryPath));
        $this->assertFalse(file_exists($directoryPath . '/file-to-be-removed'));
        $this->assertFalse(is_dir($directoryPath . '/dir-to-be-removed'));
        $this->assertFalse(file_exists($directoryPath . '/link-to-be-removed'));
    }

    public function testExceptionIsThrownIfDirectoryDoesNotExist()
    {
        $this->setExpectedException(DirectoryDoesNotExistException::class);
        $this->filesystem->removeDirectoryAndItsContent('/non-existing-directory');
    }

    public function testExceptionIsThrownIfDirectoryIsNotWritable()
    {
        $this->setExpectedException(DirectoryNotWritableException::class);
        $this->filesystem->removeDirectoryAndItsContent($this->nonWritableDirectoryPath);
    }

    public function testItSilentlyReturnsIfTheDirectoryDoesNotExist()
    {
        $this->filesystem->removeDirectoryContents('some-non-existant-directory');
        $this->assertTrue(true, 'Assert the code did not try to open a non-exitant directory throwing an exception');
    }

    public function testItThrowsAnExceptionIfTheDirectoryIsAFile()
    {
        $this->setExpectedException(
            Exception\NotADirectoryException::class,
            'The given path is not a directory: "'
        );
        
        $filePath = $directoryPath = $this->testDirectoryPath . '/existing-file';
        touch($filePath);
        $this->filesystem->removeDirectoryContents($filePath);
    }
}
