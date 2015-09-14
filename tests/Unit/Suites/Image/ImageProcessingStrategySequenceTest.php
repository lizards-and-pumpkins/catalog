<?php

namespace LizardsAndPumpkins\Image;

/**
 * @covers \LizardsAndPumpkins\Image\ImageProcessingStrategySequence
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

    public function testImageProcessorStrategyInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ImageProcessingStrategy::class, $this->strategySequence);
    }

    public function testAllStrategiesOfSequenceAreExecuted()
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
