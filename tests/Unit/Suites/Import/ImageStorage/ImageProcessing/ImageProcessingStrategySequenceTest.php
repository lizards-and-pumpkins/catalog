<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategySequence
 */
class ImageProcessingStrategySequenceTest extends TestCase
{
    /**
     * @var ImageProcessingStrategySequence
     */
    private $strategySequence;

    final protected function setUp(): void
    {
        $this->strategySequence = new ImageProcessingStrategySequence();
    }

    public function testImageProcessorStrategyInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(ImageProcessingStrategy::class, $this->strategySequence);
    }

    public function testAllStrategiesOfSequenceAreExecuted(): void
    {
        $mockStrategy1 = $this->createMock(ImageProcessingStrategy::class);
        $mockStrategy1->expects($this->once())
            ->method('processBinaryImageData');
        $mockStrategy2 = $this->createMock(ImageProcessingStrategy::class);
        $mockStrategy2->expects($this->once())
            ->method('processBinaryImageData');

        $this->strategySequence->add($mockStrategy1);
        $this->strategySequence->add($mockStrategy2);

        $this->strategySequence->processBinaryImageData('imageBinaryData');
    }
}
