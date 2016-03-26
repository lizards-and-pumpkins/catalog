<?php

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing;

use LizardsAndPumpkins\Import\FileStorage\FileStorageReader;
use LizardsAndPumpkins\Import\FileStorage\FileStorageWriter;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\UnableToCreateTargetDirectoryForProcessedImagesException;

class ImageProcessor
{
    /**
     * @var ImageProcessingStrategySequence
     */
    private $strategySequence;

    /**
     * @var FileStorageReader
     */
    private $reader;

    /**
     * @var FileStorageWriter
     */
    private $writer;

    /**
     * @var string
     */
    private $targetImageDirectoryPath;

    /**
     * @param ImageProcessingStrategySequence $strategySequence
     * @param FileStorageReader $reader
     * @param FileStorageWriter $writer
     * @param string $targetImageDirectoryPath
     */
    public function __construct(
        ImageProcessingStrategySequence $strategySequence,
        FileStorageReader $reader,
        FileStorageWriter $writer,
        $targetImageDirectoryPath
    ) {
        $this->strategySequence = $strategySequence;
        $this->reader = $reader;
        $this->writer = $writer;
        $this->targetImageDirectoryPath = $targetImageDirectoryPath;
    }

    /**
     * @param string $imageFilePath
     */
    public function process($imageFilePath)
    {
        $imageBinaryData = $this->reader->getFileContents($imageFilePath);

        $processedImageStream = $this->strategySequence->processBinaryImageData($imageBinaryData);

        $targetFilePath = $this->targetImageDirectoryPath . '/' . basename($imageFilePath);

        $this->ensureTargetDirectoryExists();

        $this->writer->putFileContents($targetFilePath, $processedImageStream);
    }

    private function ensureTargetDirectoryExists()
    {
        if (!file_exists($this->targetImageDirectoryPath)) {
            $this->createDirectory($this->targetImageDirectoryPath);
        }
    }

    /**
     * @param string $directoryPath
     */
    private function createDirectory($directoryPath)
    {
        if (!is_writable(dirname($directoryPath))) {
            $message = sprintf('Unable to create the target directory for processed images "%s"', $directoryPath);
            throw new UnableToCreateTargetDirectoryForProcessedImagesException($message);
        }
        mkdir($directoryPath, 0755, true);
    }
}
