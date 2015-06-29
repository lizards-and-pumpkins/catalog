<?php

namespace Brera\Image;

use Brera\FileStorageReader;
use Brera\FileStorageWriter;

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

    public function __construct(
        ImageProcessingStrategySequence $strategySequence,
        FileStorageReader $reader,
        FileStorageWriter $writer
    ) {
        $this->strategySequence = $strategySequence;
        $this->reader = $reader;
        $this->writer = $writer;
    }

    /**
     * @param string $imageFileName
     */
    public function process($imageFileName)
    {
        $imageBinaryData = $this->reader->getFileContents($imageFileName);

        $processedImageStream = $this->strategySequence->processBinaryImageData($imageBinaryData);

        $this->writer->putFileContents($imageFileName, $processedImageStream);
    }
}
