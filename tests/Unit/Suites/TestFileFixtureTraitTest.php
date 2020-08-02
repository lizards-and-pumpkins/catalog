<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\TestFileFixtureTrait
 */
class TestFileFixtureTraitTest extends TestCase
{
    use TestFileFixtureTrait;

    public function testFileIsCreated(): string
    {
        $file = $this->getTestFilePath();
        $this->assertFileDoesNotExist($file);
        $this->createFixtureFile($file, '');
        $this->assertFileExists($file);

        return $file;
    }

    /**
     * @depends testFileIsCreated
     * @param string $file
     */
    public function testCreatedFileIsRemoved(string $file): void
    {
        $this->assertFileDoesNotExist($file);
    }

    public function testFixtureDirectoryIsCreated(): string
    {
        $directoryPath = $this->getTestDirectoryPath();
        $this->assertFileDoesNotExist($directoryPath);
        $this->createFixtureDirectory($directoryPath);
        $this->assertFileExists($directoryPath);
        $this->assertTrue(is_dir($directoryPath));

        return $directoryPath;
    }

    /**
     * @depends testFixtureDirectoryIsCreated
     * @param string $directoryPath
     */
    public function testFixtureDirectoryIsRemoved(string $directoryPath): void
    {
        $this->assertFileDoesNotExist($directoryPath);
    }

    public function testNonExistentDirectoriesAreCreated(): string
    {
        $dir = sys_get_temp_dir() . '/non-existent-dir-' . uniqid();
        $file = $dir . '/test.file';
        $this->assertFalse(file_exists($dir));
        $this->createFixtureFile($file, '');
        $this->assertTrue(file_exists($dir));
        $this->assertTrue(is_dir($dir));

        return $dir;
    }

    /**
     * @depends testNonExistentDirectoriesAreCreated
     * @param string $dir
     */
    public function testCreatedDirectoryIsRemoved(string $dir): void
    {
        $this->assertFalse(file_exists($dir));
        $this->assertTrue(file_exists(sys_get_temp_dir()));
    }

    public function testFileWithTheGivenContentIsCreated(): void
    {
        $file = $this->getTestFilePath();
        $content = '123';
        $this->createFixtureFile($file, $content);

        $this->assertEquals($content, file_get_contents($file));
    }

    public function testFileWit0500ModeIsCreatedByDefault(): void
    {
        $file = $this->getTestFilePath();
        $this->createFixtureFile($file, '');

        $this->assertFileMode($file, '0600');
    }

    public function testFileWithGivenModeIsCreated(): void
    {
        $file = $this->getTestFilePath();
        $this->createFixtureFile($file, '', 0666);
        $this->assertFileMode($file, '0666');
    }

    public function testNonWritableFileIsCreated(): string
    {
        $file = $this->getTestFilePath();
        $this->createFixtureFile($file, '', 0000);
        $this->assertFileMode($file, '0000');

        return $file;
    }

    /**
     * @depends testNonWritableFileIsCreated
     * @param string $file
     */
    public function testNonWritableFieIsRemoved(string $file): void
    {
        $this->assertFileDoesNotExist($file);
    }

    public function testExceptionIsThrownIfFileAlreadyExists(): void
    {
        $file = $this->getTestFilePath();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Fixture file already exists');

        $this->createFixtureFile($file, '');
        $this->createFixtureFile($file, '');
    }

    public function testNonExistingTemporaryDirectoryIsReturned(): void
    {
        $this->assertFileDoesNotExist($this->getUniqueTempDir());
    }

    public function testSameTemporaryDirectoryIsReturnedOnSubsequentCallsWithinATest(): void
    {
        $dir1 = $this->getUniqueTempDir();
        $dir2 = $this->getUniqueTempDir();

        $this->assertSame($dir1, $dir2);
    }

    private function getTestFilePath(): string
    {
        return sys_get_temp_dir() . '/' . uniqid() . '.test';
    }

    private function getTestDirectoryPath(): string
    {
        return sys_get_temp_dir() . '/' . uniqid() . '.test';
    }

    private function assertFileMode(string $file, string $expected, string $message = ''): void
    {
        $modeAsString = sprintf('%o', fileperms($file));
        $this->assertEquals($expected, substr($modeAsString, - 4), $message);
    }
}
