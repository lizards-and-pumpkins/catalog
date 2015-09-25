<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\DomainEvent;

class ImageWasAddedDomainEvent implements DomainEvent
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
