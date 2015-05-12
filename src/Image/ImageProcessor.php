<?php

namespace Brera\Image;

use Brera\StaticFile;

class ImageProcessor
{
    /**
     * @var ImageProcessorCommandSequence
     */
    private $commandSequence;

    /**
     * @var StaticFile
     */
    private $fileStorage;

    public function __construct(ImageProcessorCommandSequence $commandSequence, StaticFile $fileStorage)
    {
        $this->commandSequence = $commandSequence;
        $this->fileStorage = $fileStorage;
    }

    /**
     * @param string $imageFileName
     */
    public function process($imageFileName)
    {
        $imageStream = $this->fileStorage->getFileContents($imageFileName);

        $processedImageStream = $this->commandSequence->process($imageStream);

        $this->fileStorage->putFileContents($imageFileName, $processedImageStream);
    }
}
