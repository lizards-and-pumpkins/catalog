<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing;

use LizardsAndPumpkins\Import\FileStorage\FileStorageReader;
use LizardsAndPumpkins\Import\FileStorage\FileStorageWriter;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\UnableToCreateTargetDirectoryForProcessedImagesException;
use LizardsAndPumpkins\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessor
 */
class ImageProcessorTest extends TestCase
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
     * @var ImageProcessingStrategySequence|MockObject
     */
    private $mockStrategySequence;

    /**
     * @var FileStorageReader|MockObject
     */
    private $mockFileStorageReader;

    /**
     * @var FileStorageWriter|MockObject
     */
    private $mockFileStorageWriter;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    final protected function setUp(): void
    {
        $this->targetImageDirectoryPath = $this->getUniqueTempDir() . '/test/image-processor-foo';
        $this->createFixtureDirectory($this->targetImageDirectoryPath);
        rmdir($this->targetImageDirectoryPath);
        
        $this->mockStrategySequence = $this->createMock(ImageProcessingStrategySequence::class);

        $this->mockFileStorageReader = $this->createMock(FileStorageReader::class);

        $this->mockFileStorageWriter = $this->createMock(FileStorageWriter::class);

        $this->imageProcessor = new ImageProcessor(
            $this->mockStrategySequence,
            $this->mockFileStorageReader,
            $this->mockFileStorageWriter,
            $this->targetImageDirectoryPath
        );
    }

    final protected function tearDown(): void
    {
        @chmod(dirname($this->targetImageDirectoryPath), 0700);
        parent::tearDown();
    }


    public function testImageIsProcessed(): void
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

    public function testItThrowsAnExceptionIfTheTargetDirectoryCanNotBeCreated(): void
    {
        chmod(dirname($this->targetImageDirectoryPath), 0000);
        $this->expectException(UnableToCreateTargetDirectoryForProcessedImagesException::class);
        $this->expectExceptionMessage('Unable to create the target directory for processed images "');
        $this->imageProcessor->process('will-not-get-this-far.jpg');
    }
}
