<?php

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection
 */
class ImageProcessorCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testAllProcessorsOfCollectionAreProcessed()
    {
        $processor1 = $this->createMock(ImageProcessor::class);
        $processor1->expects($this->once())
            ->method('process');
        $processor2 = $this->createMock(ImageProcessor::class);
        $processor2->expects($this->once())
            ->method('process');

        $collection = new ImageProcessorCollection();
        $collection->add($processor1);
        $collection->add($processor2);

        $collection->process('imageFilePath');
    }
}
