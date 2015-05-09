<?php

namespace Brera\ImageProcessor;

/**
 * @covers \Brera\ImageProcessor\ImageProcessorCommandSequence
 */
class ImageProcessorCommandSequenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnArrayOfImageProcessorCommands()
    {
        $stubCommand1 = $this->getMock(ImageProcessorCommand::class);
        $stubCommand2 = $this->getMock(ImageProcessorCommand::class);

        $commandSequence = new ImageProcessorCommandSequence();
        $commandSequence->addCommand($stubCommand1);
        $commandSequence->addCommand($stubCommand2);

        $result = $commandSequence->getCommands();

        $this->assertContainsOnly(ImageProcessorCommand::class, $result);
        $this->assertCount(2, $result);
        $this->assertSame($stubCommand1, $result[0]);
        $this->assertSame($stubCommand2, $result[1]);
    }
}
