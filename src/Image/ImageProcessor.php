<?php

namespace Brera\Image;

use Brera\FileStorage;

class ImageProcessor
{
    /**
     * @var ImageProcessorCommandSequence
     */
    private $commandSequence;

    /**
     * @var FileStorage
     */
    private $fileStorage;

    public function __construct(ImageProcessorCommandSequence $commandSequence, FileStorage $fileStorage)
    {
        $this->commandSequence = $commandSequence;
        $this->fileStorage = $fileStorage;
    }

    /**
     * @param string $imageFileName
     */
    public function process($imageFileName)
    {
        $imageBinaryData = $this->fileStorage->getFileContents($imageFileName);

        $processedImageStream = $this->commandSequence->execute($imageBinaryData);

        $this->fileStorage->putFileContents($imageFileName, $processedImageStream);
    }
}
