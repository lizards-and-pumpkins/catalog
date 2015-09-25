<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Image\Exception\ImageFileDoesNotExistException;

class AddImageCommand implements Command
{
    /**
     * @var string
     */
    private $imageFilePath;
    
    /**
     * @var DataVersion
     */
    private $dataVersion;

    /**
     * @param string $imageFilePath
     * @param DataVersion $dataVersion
     */
    public function __construct($imageFilePath, DataVersion $dataVersion)
    {
        if (! file_exists($imageFilePath)) {
            throw new ImageFileDoesNotExistException(
                sprintf('The image file does not exist: "%s"', $imageFilePath)
            );
        }
        $this->imageFilePath = $imageFilePath;
        $this->dataVersion = $dataVersion;
    }

    /**
     * @return string
     */
    public function getImageFilePath()
    {
        return $this->imageFilePath;
    }

    /**
     * @return DataVersion
     */
    public function getDataVersion()
    {
        return $this->dataVersion;
    }
}
