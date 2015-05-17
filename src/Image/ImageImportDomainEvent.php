<?php

namespace Brera\Image;

use Brera\DomainEvent;

class ImageImportDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    private $image;

    /**
     * @param string $image
     */
    public function __construct($image)
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }
}
