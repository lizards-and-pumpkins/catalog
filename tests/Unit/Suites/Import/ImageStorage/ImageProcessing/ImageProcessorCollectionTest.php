<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection
 */
class ImageProcessorCollectionTest extends TestCase
{
    public function testAllProcessorsOfCollectionAreProcessed()
    {
        /** @var ImageProcessor|\PHPUnit_Framework_MockObject_MockObject $processor1 */
        $processor1 = $this->createMock(ImageProcessor::class);
        $processor1->expects($this->once())
            ->method('process');

        /** @var ImageProcessor|\PHPUnit_Framework_MockObject_MockObject $processor2 */
        $processor2 = $this->createMock(ImageProcessor::class);
        $processor2->expects($this->once())
            ->method('process');

        $collection = new ImageProcessorCollection();
        $collection->add($processor1);
        $collection->add($processor2);

        $collection->process('imageFilePath');
    }
}
