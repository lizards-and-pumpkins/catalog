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
     * @param $imagePath
     * @return ImageMagicImageProcessor
     * @throws InvalidImageException
     */
    public static function fromFile($imagePath)
    {
        if (!is_readable($imagePath)) {
            throw new InvalidImageException("File \"$imagePath\" doesn't exist or is not readable.", $imagePath);
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

    public function resizeToWidth($widthToResize)
    {
        $this->processor->resizeImage($widthToResize, false, \Imagick::FILTER_LANCZOS, 1);
    }

    /**
     * @param $heightToResize
     * @return boolean
     */
    public function resizeToHeight($heightToResize)
    {
        $this->processor->resizeImage(false, $heightToResize, \Imagick::FILTER_LANCZOS, 1);
    }
}
