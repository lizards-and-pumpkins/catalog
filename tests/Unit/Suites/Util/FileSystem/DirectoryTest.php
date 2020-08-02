<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\FileSystem;

use LizardsAndPumpkins\TestFileFixtureTrait;
use LizardsAndPumpkins\Util\FileSystem\Exception\FileAlreadyExistsWithinGivenPathException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Util\FileSystem\Directory
 */
class DirectoryTest extends TestCase
{
    use TestFileFixtureTrait;

    public function testExceptionIfNonStringIsSpecifiedAsDirectoryPath(): void
    {
        $this->expectException(\TypeError::class);
        Directory::fromPath(1);
    }

    public function testExceptionIsThrownIfFileWithGivenPathAlreadyExists(): void
    {
        $filePath = $this->getUniqueTempDir() . '/' . uniqid();
        $this->createFixtureFile($filePath, '');

        $this->expectException(FileAlreadyExistsWithinGivenPathException::class);

        Directory::fromPath($filePath);
    }

    public function testFalseIsReturnedIfDirectoryIsNotReadable(): void
    {
        $directory = Directory::fromPath('/some-not-existing-directory');
        $this->assertFalse($directory->isReadable());
    }

    public function testTrueIsReturnedIfDirectoryIsReadable(): void
    {
        $directory = Directory::fromPath(sys_get_temp_dir());
        $this->assertTrue($directory->isReadable());
    }

    public function testDirectoryPathIsReturned(): void
    {
        $directoryPath = $this->getUniqueTempDir();
        $this->createFixtureDirectory($directoryPath);

        $directory = Directory::fromPath($directoryPath);
        $result = $directory->getPath();

        $this->assertEquals($directoryPath, $result);
    }
}
