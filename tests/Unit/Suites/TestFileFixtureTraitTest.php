<?php

namespace LizardsAndPumpkins;

/**
 * @covers \LizardsAndPumpkins\TestFileFixtureTrait
 */
class TestFileFixtureTraitTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @return string
     */
    public function testFileIsCreated()
    {
        $file = $this->getTestFilePath();
        $this->assertFileNotExists($file);
        $this->createFixtureFile($file, '');
        $this->assertFileExists($file);

        return $file;
    }

    /**
     * @depends testFileIsCreated
     * @param string $file
     */
    public function testCreatedFileIsRemoved($file)
    {
        $this->assertFileNotExists($file);
    }

    /**
     * @return string
     */
    public function testFixtureDirectoryIsCreated()
    {
        $directoryPath = $this->getTestDirectoryPath();
        $this->assertFileNotExists($directoryPath);
        $this->createFixtureDirectory($directoryPath);
        $this->assertFileExists($directoryPath);
        $this->assertTrue(is_dir($directoryPath));

        return $directoryPath;
    }

    /**
     * @depends testFixtureDirectoryIsCreated
     * @param string $directoryPath
     */
    public function testFixtureDirectoryIsRemoved($directoryPath)
    {
        $this->assertFileNotExists($directoryPath);
    }

    /**
     * @return string
     */
    public function testNonExistentDirectoriesAreCreated()
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
    public function testCreatedDirectoryIsRemoved($dir)
    {
        $this->assertFalse(file_exists($dir));
        $this->assertTrue(file_exists(sys_get_temp_dir()));
    }

    public function testFileWithTheGivenContentIsCreated()
    {
        $file = $this->getTestFilePath();
        $content = '123';
        $this->createFixtureFile($file, $content);

        $this->assertEquals($content, file_get_contents($file));
    }

    public function testFileWit0500ModeIsCreatedByDefault()
    {
        $file = $this->getTestFilePath();
        $this->createFixtureFile($file, '');

        $this->assertFileMode($file, 0600);
    }

    public function testFileWithGivenModeIsCreated()
    {
        $file = $this->getTestFilePath();
        $this->createFixtureFile($file, '', 0666);
        $this->assertFileMode($file, 0666);
    }

    /**
     * @return string
     */
    public function testNonWritableFileIsCreated()
    {
        $file = $this->getTestFilePath();
        $this->createFixtureFile($file, '', 0000);
        $this->assertFileMode($file, 0000);

        return $file;
    }

    /**
     * @depends testNonWritableFileIsCreated
     * @param string $file
     */
    public function testNonWritableFieIsRemoved($file)
    {
        $this->assertFileNotExists($file);
    }

    public function testExceptionIsThrownIfFileAlreadyExists()
    {
        $file = $this->getTestFilePath();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Fixture file already exists');

        $this->createFixtureFile($file, '');
        $this->createFixtureFile($file, '');
    }

    public function testNonExistingTemporaryDirectoryIsReturned()
    {
        $this->assertFileNotExists($this->getUniqueTempDir());
    }

    public function testSameTemporaryDirectoryIsReturnedOnSubsequentCallsWithinATest()
    {
        $dir1 = $this->getUniqueTempDir();
        $dir2 = $this->getUniqueTempDir();

        $this->assertSame($dir1, $dir2);
    }

    /**
     * @return string
     */
    private function getTestFilePath()
    {
        return sys_get_temp_dir() . '/' . uniqid() . '.test';
    }

    /**
     * @return string
     */
    private function getTestDirectoryPath()
    {
        return sys_get_temp_dir() . '/' . uniqid() . '.test';
    }

    /**
     * @param string $file
     * @param string $expected
     * @param string $message
     */
    private function assertFileMode($file, $expected, $message = '')
    {
        $expectedAsString = is_string($expected) ?
            $expected :
            sprintf('%o', $expected);
        $modeAsString = sprintf('%o', fileperms($file));
        $this->assertEquals((string)$expectedAsString, substr($modeAsString, -4), $message);
    }
}
