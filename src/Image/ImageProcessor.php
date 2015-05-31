<?php

namespace Brera\Image;

use Brera\FileStorageReader;
use Brera\FileStorageWriter;

class ImageProcessor
{
    /**
     * @var ImageProcessorCommandSequence
     */
    private $commandSequence;

    /**
     * @var FileStorageReader
     */
    private $reader;

    /**
     * @var FileStorageWriter
     */
    private $writer;

    public function __construct(
        ImageProcessorCommandSequence $commandSequence,
        FileStorageReader $reader,
        FileStorageWriter $writer
    ) {
        $this->commandSequence = $commandSequence;
        $this->reader = $reader;
        $this->writer = $writer;
    }

    /**
     * @param string $imageFileName
     */
    public function process($imageFileName)
    {
        $imageBinaryData = $this->reader->getFileContents($imageFileName);

        $processedImageStream = $this->commandSequence->execute($imageBinaryData);

        $this->writer->putFileContents($imageFileName, $processedImageStream);
    }
}
