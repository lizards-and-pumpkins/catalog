<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\FileStorageReader;
use LizardsAndPumpkins\FileStorageWriter;
use LizardsAndPumpkins\Image\Exception\UnableToCreateTargetDirectoryForProcessedImagesException;
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

    /**
     * @var string
     */
    private $dummyImageFilePath = '/imageFilePath';

    /**
     * @var ImageProcessingStrategySequence|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockStrategySequence;

    /**
     * @var FileStorageReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockFileStorageReader;

    /**
     * @var FileStorageWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockFileStorageWriter;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    protected function setUp()
    {
        $this->targetImageDirectoryPath = $this->getUniqueTempDir() . '/test/image-processor-foo';
        $this->createFixtureDirectory($this->targetImageDirectoryPath);
        rmdir($this->targetImageDirectoryPath);
        
        $this->mockStrategySequence = $this->getMock(ImageProcessingStrategySequence::class, [], [], '', false);

        $this->mockFileStorageReader = $this->getMock(FileStorageReader::class);

        $this->mockFileStorageWriter = $this->getMock(FileStorageWriter::class);

        $this->imageProcessor = new ImageProcessor(
            $this->mockStrategySequence,
            $this->mockFileStorageReader,
            $this->mockFileStorageWriter,
            $this->targetImageDirectoryPath
        );
    }

    protected function tearDown()
    {
        @chmod(dirname($this->targetImageDirectoryPath), 0700);
        parent::tearDown();
    }


    public function testImageIsProcessed()
    {
        $this->mockFileStorageReader->expects($this->once())->method('getFileContents')
            ->with($this->dummyImageFilePath);

        $this->mockFileStorageWriter->expects($this->once())
            ->method('putFileContents')
            ->with($this->targetImageDirectoryPath . '/' . basename($this->dummyImageFilePath));

        $this->mockStrategySequence->expects($this->once())
            ->method('processBinaryImageData');
        
        $this->imageProcessor->process($this->dummyImageFilePath);
        
        $this->assertFileExists($this->targetImageDirectoryPath);
    }

    public function testItThrowsAnExceptionIfTheTargetDirectoryCanNotBeCreated()
    {
        chmod(dirname($this->targetImageDirectoryPath), 0000);
        $this->setExpectedException(
            UnableToCreateTargetDirectoryForProcessedImagesException::class,
            'Unable to create the target directory for processed images "'
        );
        $this->imageProcessor->process('will-not-get-this-far.jpg');

        $this->assertFileExists($this->targetImageDirectoryPath);
    }
}
