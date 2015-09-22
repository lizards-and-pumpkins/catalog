<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\FileStorageReader;
use LizardsAndPumpkins\FileStorageWriter;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Image\ImageProcessor
 */
class ImageProcessorTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var string
     */
    private $targetImageDirectoryPath;

    protected function setUp()
    {
        $this->targetImageDirectoryPath = $this->getUniqueTempDir() . '/test/image-processor-foo';
        $this->createFixtureDirectory($this->targetImageDirectoryPath);
        rmdir($this->targetImageDirectoryPath);
    }
    
    public function testImageIsProcessed()
    {
        $dummyImageFilePath = '/imageFilePath';

        $mockStrategySequence = $this->getMock(ImageProcessingStrategySequence::class, [], [], '', false);
        $mockStrategySequence->expects($this->once())
            ->method('processBinaryImageData');

        $mockFileStorageReader = $this->getMock(FileStorageReader::class);
        $mockFileStorageReader->expects($this->once())
            ->method('getFileContents')
            ->with($dummyImageFilePath);

        $mockFileStorageWriter = $this->getMock(FileStorageWriter::class);
        $mockFileStorageWriter->expects($this->once())
            ->method('putFileContents')
            ->with($this->targetImageDirectoryPath . '/' . basename($dummyImageFilePath));

        $imageProcessor = new ImageProcessor(
            $mockStrategySequence,
            $mockFileStorageReader,
            $mockFileStorageWriter,
            $this->targetImageDirectoryPath
        );
        $imageProcessor->process($dummyImageFilePath);
        
        $this->assertFileExists($this->targetImageDirectoryPath);
    }
}
