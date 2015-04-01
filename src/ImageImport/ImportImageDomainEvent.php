<?php

namespace Brera\ImageImport;

use Brera\DomainEvent;
use Brera\ImageProcessor\InvalidImageException;

class ImportImageDomainEvent implements DomainEvent
{
    /**
     * @var string
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
        self::validateImages($images);

        return new self($images);
    }

    /**
     * @param array $images
     */
    private static function validateImages(array $images)
    {
        foreach ($images as $image) {
            if (!is_string($image)) {
                throw new InvalidImageException('Passed image is no string.');
            }
            if (!is_readable($image)) {
                throw new InvalidImageException(sprintf('Image "%s" is not readable.', $image));
            }
        }
    }

    /**
     * @return string[]
     */
    public function getImages()
    {
        return $this->images;
    }
}
