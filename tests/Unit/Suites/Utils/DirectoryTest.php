<?php

namespace LizardsAndPumpkins\Utils;

use LizardsAndPumpkins\TestFileFixtureTrait;
use LizardsAndPumpkins\Utils\Exception\FileAlreadyExistsWithinGivenPathException;
use LizardsAndPumpkins\Utils\Exception\InvalidDirectoryPathException;

/**
 * @covers \LizardsAndPumpkins\Utils\Directory
 */
class DirectoryTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    public function testExceptionIfNonStringIsSpecifiedAsDirectoryPath()
    {
        $this->expectException(InvalidDirectoryPathException::class);
        Directory::fromPath(1);
    }

    public function testExceptionIsThrownIfFileWithGivenPathAlreadyExists()
    {
        $filePath = $this->getUniqueTempDir() . '/' . uniqid();
        $this->createFixtureFile($filePath, '');

        $this->expectException(FileAlreadyExistsWithinGivenPathException::class);

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
