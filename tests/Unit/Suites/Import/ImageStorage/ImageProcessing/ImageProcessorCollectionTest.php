<?php

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessor;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection
 */
class ImageProcessorCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testAllProcessorsOfCollectionAreProcessed()
    {
        $processor1 = $this->getMock(ImageProcessor::class, [], [], '', false);
        $processor1->expects($this->once())
            ->method('process');
        $processor2 = $this->getMock(ImageProcessor::class, [], [], '', false);
        $processor2->expects($this->once())
            ->method('process');

        $collection = new ImageProcessorCollection();
        $collection->add($processor1);
        $collection->add($processor2);

        $collection->process('imageFilePath');
    }
}
