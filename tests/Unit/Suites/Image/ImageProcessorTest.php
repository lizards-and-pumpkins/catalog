<?php

namespace Brera\Image;

use Brera\FileStorageReader;
use Brera\FileStorageWriter;

/**
 * @covers \Brera\Image\ImageProcessor
 */
class ImageProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldProcessImage()
    {
        $dummyImageFilename = 'imageFilename';

        $mockStrategySequence = $this->getMock(ImageProcessingStrategySequence::class, [], [], '', false);
        $mockStrategySequence->expects($this->once())
            ->method('processBinaryImageData');

        $mockFileStorageReader = $this->getMock(FileStorageReader::class);
        $mockFileStorageReader->expects($this->once())
            ->method('getFileContents')
            ->with($dummyImageFilename);

        $mockFileStorageWriter = $this->getMock(FileStorageWriter::class);
        $mockFileStorageWriter->expects($this->once())
            ->method('putFileContents')
            ->with($dummyImageFilename);

        $imageProcessor = new ImageProcessor($mockStrategySequence, $mockFileStorageReader, $mockFileStorageWriter);
        $imageProcessor->process($dummyImageFilename);
    }
}
