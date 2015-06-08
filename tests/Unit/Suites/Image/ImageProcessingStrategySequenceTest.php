<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageProcessingStrategySequence
 */
class ImageProcessingStrategySequenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageProcessingStrategySequence
     */
    private $strategySequence;

    protected function setUp()
    {
        $this->strategySequence = new ImageProcessingStrategySequence();
    }

    /**
     * @test
     */
    public function itShouldImplementImageProcessorStrategyInterface()
    {
        $this->assertInstanceOf(ImageProcessingStrategy::class, $this->strategySequence);
    }

    /**
     * @test
     */
    public function itShouldExecuteAllStrategiesOfASequence()
    {
        $mockStrategy1 = $this->getMock(ImageProcessingStrategy::class);
        $mockStrategy1->expects($this->once())
            ->method('processBinaryImageData');
        $mockStrategy2 = $this->getMock(ImageProcessingStrategy::class);
        $mockStrategy2->expects($this->once())
            ->method('processBinaryImageData');

        $this->strategySequence->add($mockStrategy1);
        $this->strategySequence->add($mockStrategy2);

        $this->strategySequence->processBinaryImageData('imageBinaryData');
    }
}
