<?php

namespace Brera\ImageProcessor;

class ImageMagicImageProcessor implements ImageProcessor
{
    /**
     * @var \Imagick
     */
    private $processor;

    /**
     * @param string $imagePath
     * @throws InvalidImageException
     */
    public function __construct($imagePath)
    {
        $this->processor = new \Imagick($imagePath);
    }

    /**
     * @param string $imagePath
     * @return ImageMagicImageProcessor
     * @throws InvalidImageException
     */
    public static function fromFile($imagePath)
    {
        if (!is_readable($imagePath)) {
            throw new InvalidImageException(sprintf('"File "%s" doesn\'t exist or is not readable', $imagePath));
        }

        return new self($imagePath);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function saveAsFile($path)
    {
        return $this->processor->writeImage($path);
    }

    /**
     * @param int $widthToResize
     * @return bool
     */
    public function resizeToWidth($widthToResize)
    {
        return $this->processor->resizeImage($widthToResize, false, \Imagick::FILTER_LANCZOS, 1);
    }

    /**
     * @param int $heightToResize
     * @return boolean
     */
    public function resizeToHeight($heightToResize)
    {
        $this->processor->resizeImage(false, $heightToResize, \Imagick::FILTER_LANCZOS, 1);
    }
}
