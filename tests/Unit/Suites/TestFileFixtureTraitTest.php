<?php


namespace Brera;

/**
 * @covers \Brera\TestFileFixtureTrait
 */
class TestFileFixtureTraitTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @return string
     */
    private function getTestFilename()
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

    /**
     * @test
     */
    public function itShouldCreateAFile()
    {
        $file = $this->getTestFilename();
        $this->assertFileNotExists($file);
        $this->createFixtureFile($file, '');
        $this->assertFileExists($file);
        return $file;
    }

    /**
     * @test
     * @depends itShouldCreateAFile
     * @param string $file
     */
    public function itShouldRemoveFilesItCreated($file)
    {
        $this->assertFileNotExists($file);
    }

    /**
     * @test
     */
    public function itShouldCreateNonExistentDirectories()
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
     * @test
     * @depends itShouldCreateNonExistentDirectories
     * @param string $dir
     */
    public function itShouldRemoveDirectoriesItCreated($dir)
    {
        $this->assertFalse(file_exists($dir));
        $this->assertTrue(file_exists(sys_get_temp_dir()));
    }

    /**
     * @test
     */
    public function itShouldCreateTheFileWithTheGivenContent()
    {
        $file = $this->getTestFilename();
        $content = '123';
        $this->createFixtureFile($file, $content);
        $this->assertEquals($content, file_get_contents($file));
    }

    /**
     * @test
     */
    public function itShouldCreateAFileWithTheDefaultMode0500()
    {
        $file = $this->getTestFilename();
        $this->createFixtureFile($file, '');
        $this->assertFileMode($file, 0500);
    }

    /**
     * @test
     */
    public function itShouldCreateAFileWithAGivenMode()
    {
        $file = $this->getTestFilename();
        $this->createFixtureFile($file, '', 0666);
        $this->assertFileMode($file, 0666);
    }

    /**
     * @test
     */
    public function itShouldCreateFilesWithoutWritePermissionIfSpecified()
    {
        $file = $this->getTestFilename();
        $this->createFixtureFile($file, '', 0000);
        $this->assertFileMode($file, 0000);
        return $file;
    }

    /**
     * @test
     * @depends itShouldCreateFilesWithoutWritePermissionIfSpecified
     * @param string $file
     */
    public function itShouldRemoveFilesItCreatedWithoutWritePermissions($file)
    {
        $this->assertFileNotExists($file);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Fixture file already exists
     */
    public function itShouldThrowAnExceptionIfAFileAlreadyExists()
    {
        $file = $this->getTestFilename();
        $this->createFixtureFile($file, '');
        $this->createFixtureFile($file, '');
    }

    /**
     * @test
     */
    public function itShouldReturnANonExistingTempDir()
    {
        $this->assertFileNotExists($this->getUniqueTempDir());
    }

    /**
     * @test
     */
    public function itShouldAlwaysReturnTheSameTempDirOnSubsequentCallsWithinATest()
    {
        $dir1 = $this->getUniqueTempDir();
        $dir2 = $this->getUniqueTempDir();
        $this->assertSame($dir1, $dir2);
    }
}
