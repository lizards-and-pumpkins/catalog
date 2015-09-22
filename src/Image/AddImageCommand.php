<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\Image\Exception\ImageFileDoesNotExistException;

class AddImageCommand implements Command
{
    /**
     * @var string
     */
    private $imageFilePath;

    /**
     * @param string $imageFilePath
     */
    public function __construct($imageFilePath)
    {
        if (! file_exists($imageFilePath)) {
            throw new ImageFileDoesNotExistException(
                sprintf('The image file does not exist: "%s"', $imageFilePath)
            );
        }
        $this->imageFilePath = $imageFilePath;
    }

    /**
     * @return string
     */
    public function getImageFilePath()
    {
        return $this->imageFilePath;
    }
}
