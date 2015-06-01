<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageProcessorInstructionSequence
 */
class ImageProcessorInstructionSequenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageProcessorInstructionSequence
     */
    private $instructionSequence;

    protected function setUp()
    {
        $this->instructionSequence = new ImageProcessorInstructionSequence();
    }

    /**
     * @test
     */
    public function itShouldImplementImageProcessorInstructionInterface()
    {
        $this->assertInstanceOf(ImageProcessorInstruction::class, $this->instructionSequence);
    }

    /**
     * @test
     */
    public function itShouldExecuteAllInstructionsOfASequence()
    {
        $mockInstruction1 = $this->getMock(ImageProcessorInstruction::class);
        $mockInstruction1->expects($this->once())
            ->method('execute');
        $mockInstruction2 = $this->getMock(ImageProcessorInstruction::class);
        $mockInstruction2->expects($this->once())
            ->method('execute');

        $this->instructionSequence->addInstruction($mockInstruction1);
        $this->instructionSequence->addInstruction($mockInstruction2);

        $this->instructionSequence->execute('imageBinaryData');
    }
}
