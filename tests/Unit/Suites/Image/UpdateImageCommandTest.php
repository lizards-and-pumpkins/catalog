<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\Command;

/**
 * @covers \LizardsAndPumpkins\Image\UpdateImageCommand
 */
class UpdateImageCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummyImageFileName = 'foo.png';

    /**
     * @var UpdateImageCommand
     */
    private $command;

    protected function setUp()
    {
        $this->command = new UpdateImageCommand($this->dummyImageFileName);
    }

    public function testCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testImageFileNameIsReturned()
    {
        $result = $this->command->getImageFileName();
        $this->assertSame($this->dummyImageFileName, $result);
    }
}
