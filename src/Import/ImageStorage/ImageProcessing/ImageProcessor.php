<?php

declare(strict_types = 1);

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

    public function __construct(
        ImageProcessingStrategySequence $strategySequence,
        FileStorageReader $reader,
        FileStorageWriter $writer,
        string $targetImageDirectoryPath
    ) {
        $this->strategySequence = $strategySequence;
        $this->reader = $reader;
        $this->writer = $writer;
        $this->targetImageDirectoryPath = $targetImageDirectoryPath;
    }

    public function process(string $imageFilePath)
    {
        $imageBinaryData = $this->reader->getFileContents($imageFilePath);
        $processedImageStream = $this->strategySequence->processBinaryImageData($imageBinaryData);
        $targetFilePath = $this->targetImageDirectoryPath . '/' . basename($imageFilePath);

        $this->ensureTargetDirectoryExists();

        $this->writer->putFileContents($targetFilePath, $processedImageStream);
    }

    private function ensureTargetDirectoryExists()
    {
        if (! file_exists($this->targetImageDirectoryPath)) {
            $this->createDirectory($this->targetImageDirectoryPath);
        }
    }

    private function createDirectory(string $directoryPath)
    {
        if (!@mkdir($directoryPath, 0755, true) || ! is_dir(dirname($directoryPath))) {
            $message = sprintf('Unable to create the target directory for processed images "%s"', $directoryPath);
            throw new UnableToCreateTargetDirectoryForProcessedImagesException($message);
        }
    }
}
