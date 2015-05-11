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
    private $testDirectory;

    protected function setUp()
    {
        $this->filesystem = new LocalFilesystem();

        $this->testDirectory = sys_get_temp_dir() . '/brera-local-filesystem-test';

        if (!is_dir($this->testDirectory)) {
            mkdir($this->testDirectory);
        }
    }

    protected function tearDown()
    {
        $directoryIterator = new \RecursiveDirectoryIterator($this->testDirectory, \FilesystemIterator::SKIP_DOTS);

        foreach (new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }

        rmdir($this->testDirectory);
    }

    /**
     * @test
     */
    public function itShouldRemoveDirectoryAndItsContent()
    {
        $directoryPath = $this->testDirectory . '/directory-to-be-removed';

        mkdir($directoryPath);
        touch($directoryPath . '/file');
        mkdir($directoryPath . '/dir');
        symlink($directoryPath . '/file', $directoryPath . '/link');

        $this->filesystem->removeDirectoryAndItsContent($directoryPath);

        $this->assertFalse(is_dir($directoryPath));
    }
}
