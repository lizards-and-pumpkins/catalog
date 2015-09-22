<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\Image\Exception\ImageFileDoesNotExistException;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Image\AddImageCommand
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
     * @var AddImageCommand
     */
    private $command;

    protected function setUp()
    {
        $this->fixtureDirectoryPath = $this->getUniqueTempDir();
        $this->imageFilePath = $this->fixtureDirectoryPath . '/foo.png';
        $this->createFixtureDirectory($this->fixtureDirectoryPath);
        $this->createFixtureFile($this->imageFilePath, '');
        $this->command = new AddImageCommand($this->imageFilePath);
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
        $this->setExpectedException(
            ImageFileDoesNotExistException::class,
            'The image file does not exist: "foo.png"'
        );
        new AddImageCommand('foo.png');
    }
}
