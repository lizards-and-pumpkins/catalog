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

        $mockCommandSequence = $this->getMock(ImageProcessorCommandSequence::class, [], [], '', false);
        $mockCommandSequence->expects($this->once())
            ->method('execute');

        $mockFileStorageReader = $this->getMock(FileStorageReader::class);
        $mockFileStorageReader->expects($this->once())
            ->method('getFileContents')
            ->with($dummyImageFilename);

        $mockFileStorageWriter = $this->getMock(FileStorageWriter::class);
        $mockFileStorageWriter->expects($this->once())
            ->method('putFileContents')
            ->with($dummyImageFilename);

        $imageProcessor = new ImageProcessor($mockCommandSequence, $mockFileStorageReader, $mockFileStorageWriter);
        $imageProcessor->process($dummyImageFilename);
    }
}
