<?php

namespace Brera\Image;

use Brera\FileStorageReader;
use Brera\FileStorageWriter;

class ImageProcessor
{
    /**
     * @var ImageProcessorInstructionSequence
     */
    private $instructionSequence;

    /**
     * @var FileStorageReader
     */
    private $reader;

    /**
     * @var FileStorageWriter
     */
    private $writer;

    public function __construct(
        ImageProcessorInstructionSequence $instructionSequence,
        FileStorageReader $reader,
        FileStorageWriter $writer
    ) {
        $this->instructionSequence = $instructionSequence;
        $this->reader = $reader;
        $this->writer = $writer;
    }

    /**
     * @param string $imageFileName
     */
    public function process($imageFileName)
    {
        $imageBinaryData = $this->reader->getFileContents($imageFileName);

        $processedImageStream = $this->instructionSequence->execute($imageBinaryData);

        $this->writer->putFileContents($imageFileName, $processedImageStream);
    }
}
