<?php

namespace Brera\Utils;

use Brera\TestFileFixtureTrait;

/**
 * @covers \Brera\Directory
 */
class DirectoryTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    public function testExceptionIfNonStringIsSpecifiedAsDirectoryPath()
    {
        $this->setExpectedException(InvalidDirectoryPathException::class);
        Directory::fromPath(1);
    }

    public function testExceptionIsThrownIfFileWithGivenPathAlreadyExists()
    {
        $filePath = $this->getUniqueTempDir() . '/' . uniqid();
        $this->createFixtureFile($filePath, '');

        $this->setExpectedException(FileAlreadyExistsWithinGivenPathException::class);

        Directory::fromPath($filePath);
    }

    public function testFalseIsReturnedIfDirectoryIsNotReadable()
    {
        $directory = Directory::fromPath('/some-not-existing-directory');
        $this->assertFalse($directory->isReadable());
    }

    public function testTrueIsReturnedIfDirectoryIsReadable()
    {
        $directory = Directory::fromPath(sys_get_temp_dir());
        $this->assertTrue($directory->isReadable());
    }

    public function testDirectoryPathIsReturned()
    {
        $directoryPath = $this->getUniqueTempDir();
        $this->createFixtureDirectory($directoryPath);

        $directory = Directory::fromPath($directoryPath);
        $result = $directory->getPath();

        $this->assertEquals($directoryPath, $result);
    }
}
