<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageProcessorCommandSequence
 */
class ImageProcessorCommandSequenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageProcessorCommandSequence
     */
    private $commandSequence;

    protected function setUp()
    {
        $this->commandSequence = new ImageProcessorCommandSequence();
    }

    /**
     * @test
     */
    public function itShouldImplementImageProcessorCommandInterface()
    {
        $this->assertInstanceOf(ImageProcessorCommand::class, $this->commandSequence);
    }

    /**
     * @test
     */
    public function itShouldExecuteAllCommandsOfSequence()
    {
        $mockCommand1 = $this->getMock(ImageProcessorCommand::class);
        $mockCommand1->expects($this->once())
            ->method('execute');
        $mockCommand2 = $this->getMock(ImageProcessorCommand::class);
        $mockCommand2->expects($this->once())
            ->method('execute');

        $this->commandSequence->addCommand($mockCommand1);
        $this->commandSequence->addCommand($mockCommand2);

        $this->commandSequence->execute('imageBinaryData');
    }
}
