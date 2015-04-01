<?php

namespace Brera\ImageImport;

use Brera\DomainEvent;

class ImportImageDomainEvent implements DomainEvent
{
    /**
     * @var mixed[]
     */
    private $images;

    /**
     * @param $images
     */
    private function __construct(array $images)
    {
        $this->images = $images;
    }

    /**
     * @param string[] $images
     * @return ImportImageDomainEvent
     */
    public static function fromImages(array $images)
    {
        return new self($images);
    }

    /**
     * @return string[]
     */
    public function getImages()
    {
        return $this->images;
    }
}
