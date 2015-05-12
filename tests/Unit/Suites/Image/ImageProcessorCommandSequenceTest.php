<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageProcessorCommandSequence
 */
class ImageProcessorCommandSequenceTest extends \PHPUnit_Framework_TestCase
{
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

        $commandSequence = new ImageProcessorCommandSequence();
        $commandSequence->addCommand($mockCommand1);
        $commandSequence->addCommand($mockCommand2);

        $commandSequence->process('imageFilename');
    }
}
