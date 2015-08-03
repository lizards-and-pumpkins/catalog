<?php

namespace Brera\Image;

use Brera\DomainEvent;

class ImageWasUpdatedDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    private $imageFileName;

    /**
     * @param string $imageFileName
     */
    public function __construct($imageFileName)
    {
        $this->imageFileName = $imageFileName;
    }

    /**
     * @return string
     */
    public function getImageFileName()
    {
        return $this->imageFileName;
    }
}
