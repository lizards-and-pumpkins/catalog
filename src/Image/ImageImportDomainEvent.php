<?php

namespace Brera\Image;

use Brera\DomainEvent;

class ImageImportDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    private $imageFilename;

    /**
     * @param string $imageFilename
     */
    public function __construct($imageFilename)
    {
        $this->imageFilename = $imageFilename;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->imageFilename;
    }
}
