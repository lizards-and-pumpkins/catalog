<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\DomainEvent;

class ImageWasAddedDomainEvent implements DomainEvent
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
