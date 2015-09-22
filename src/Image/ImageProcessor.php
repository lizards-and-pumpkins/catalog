<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\FileStorageReader;
use LizardsAndPumpkins\FileStorageWriter;

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

        if (!is_dir($this->targetImageDirectoryPath)) {
            mkdir($this->targetImageDirectoryPath, 0755, true);
        }

        $this->writer->putFileContents($targetFilePath, $processedImageStream);
    }
}
