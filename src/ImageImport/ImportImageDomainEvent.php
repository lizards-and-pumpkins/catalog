<?php

namespace Brera\ImageImport;

use Brera\DomainEvent;

class ImportImageDomainEvent implements DomainEvent
{
    /**
     * @var string[]
     */
    private $images;

    /**
     * @param string[] $images
     */
    public function __construct(array $images)
    {
        $this->images = $images;
    }

    /**
     * @return string[]
     */
    public function getImages()
    {
        return $this->images;
    }
}
