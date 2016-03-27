<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Import\Image\AddImageCommand;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Image\ImageFileDoesNotExistException;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Import\Image\AddImageCommand
 */
class AddImageCommandTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var string
     */
    private $fixtureDirectoryPath;

    /**
     * @var string
     */
    private $imageFilePath;

    /**
     * @var DataVersion|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataVersion;
    
    /**
     * @var AddImageCommand
     */
    private $command;

    protected function setUp()
    {
        $this->fixtureDirectoryPath = $this->getUniqueTempDir();
        $this->imageFilePath = $this->fixtureDirectoryPath . '/foo.png';
        $this->createFixtureDirectory($this->fixtureDirectoryPath);
        $this->createFixtureFile($this->imageFilePath, '');
        $this->stubDataVersion = $this->getMock(DataVersion::class, [], [], '', false);
        $this->command = new AddImageCommand($this->imageFilePath, $this->stubDataVersion);
    }

    public function testCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testImageFileNameIsReturned()
    {
        $result = $this->command->getImageFilePath();
        $this->assertSame($this->imageFilePath, $result);
    }

    public function testItThrowsAnExceptionIfTheImageDoesNotExist()
    {
        $this->expectException(ImageFileDoesNotExistException::class);
        $this->expectExceptionMessage('The image file does not exist: "foo.png"');
        new AddImageCommand('foo.png', $this->stubDataVersion);
    }

    public function testItReturnsTheInjectedDataVersion()
    {
        $this->assertSame($this->stubDataVersion, $this->command->getDataVersion());
    }
}
