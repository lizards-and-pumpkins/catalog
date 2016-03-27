<?php

namespace LizardsAndPumpkins\Util\FileSystem;

use LizardsAndPumpkins\Util\FileSystem\Exception\DirectoryDoesNotExistException;
use LizardsAndPumpkins\Util\FileSystem\Exception\DirectoryNotWritableException;
use LizardsAndPumpkins\Util\FileSystem\Exception\NotADirectoryException;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystem;

/**
 * @covers \LizardsAndPumpkins\Util\FileSystem\LocalFilesystem
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
        $this->expectException(DirectoryDoesNotExistException::class);
        $this->filesystem->removeDirectoryAndItsContent('/non-existing-directory');
    }

    public function testExceptionIsThrownIfDirectoryIsNotWritable()
    {
        $this->expectException(DirectoryNotWritableException::class);
        $this->filesystem->removeDirectoryAndItsContent($this->nonWritableDirectoryPath);
    }

    public function testItSilentlyReturnsIfTheDirectoryDoesNotExist()
    {
        $this->filesystem->removeDirectoryContents('some-non-existent-directory');
        $this->assertTrue(true, 'Assert the code did not try to open a non-existent directory throwing an exception');
    }

    public function testItThrowsAnExceptionIfTheDirectoryIsAFile()
    {
        $this->expectException(NotADirectoryException::class);
        $this->expectExceptionMessage('The given path is not a directory: "');
        
        $filePath = $directoryPath = $this->testDirectoryPath . '/existing-file';
        touch($filePath);
        $this->filesystem->removeDirectoryContents($filePath);
    }

    /**
     * @dataProvider getRelativePath
     * @param string $basePath
     * @param string $path
     * @param string $expected
     */
    public function testRelativePathIsReturned($basePath, $path, $expected)
    {
        $this->assertSame($expected, $this->filesystem->getRelativePath($basePath, $path));
    }

    /**
     * @return array[]
     */
    public function getRelativePath()
    {
        return [
            'path within bp' => ['/base/path', '/base/path/file', 'file'],
            'path within bp, bp with /' => ['/base/path/', '/base/path/file', 'file'],
            'path within bp, path with /' => ['/base/path/', '/base/path/file/', 'file/'],

            'path eq bp, with /' => ['/base/path/', '/base/path/', ''],
            'path eq bp, no /' => ['/base/path', '/base/path', ''],
            'path eq bp, path no /' => ['/base/path/', '/base/path', ''],
            'path eq bp, path with /' => ['/base/path', '/base/path/', ''],

            'path relative, path no /' => ['/base/path', 'relative/path', 'relative/path'],
            'path relative, path no /, bp with /' => ['/base/path/', 'relative/file', 'relative/file'],
            'path relative, path with /' => ['/base/path', 'relative/file/', 'relative/file/'],
            'path relative, path with /, bp with /' => ['/base/path/', 'relative/file/', 'relative/file/'],

            'bp is root' => ['/', '/path/to/file', 'path/to/file'],
            'path is root' => ['/base/path/dir', '/', '../../../'],

            'path is parent of bp' => ['/base/path', '/base', '..'],
            'path is parent of bp, path with /' => ['/base/path', '/base/', '../'],
            'path is grandparent of bp' => ['/base/path/dir', '/base', '../..'],
            'path is grandparent of bp, path with /' => ['/base/path/dir', '/base/', '../../'],

            'path one up one down' => ['/base/path/dir', '/base/path/another-dir', '../another-dir'],
            'path one up one down, path has /' => ['/base/path/dir', '/base/path/another-dir/', '../another-dir/'],

            'no shared parent' => ['/one/dir/path', '/another/dir/path', '../../../another/dir/path'],
        ];
    }
}
