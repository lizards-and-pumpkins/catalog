<?php

namespace Brera\Image;

use Brera\FileStorage;

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

        $mockFileStorage = $this->getMock(FileStorage::class);
        $mockFileStorage->expects($this->once())
            ->method('getFileContents')
            ->with($dummyImageFilename);
        $mockFileStorage->expects($this->once())
            ->method('putFileContents')
            ->with($dummyImageFilename);

        (new ImageProcessor($mockCommandSequence, $mockFileStorage))->process($dummyImageFilename);
    }
}
