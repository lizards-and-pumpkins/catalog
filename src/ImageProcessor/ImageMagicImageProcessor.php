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
     */
    private function __construct($imagePath)
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
        if (is_scalar($path) && !is_string($path)) {
            throw new ImageSaveFailedException(sprintf('Image could not be saved, "%s" is not string.', $path));
        }

        if (!is_string($path)) {
            throw new ImageSaveFailedException('Image could not be saved, $path is no string.');
        }

        try {
            return $this->processor->writeImage($path);
        } catch (\ImagickException $e) {
            throw new ImageSaveFailedException($e->getMessage());
        }

    }

    /**
     * @param int $widthToResize
     */
    public function resizeToWidth($widthToResize)
    {
        $this->processor->resizeImage($widthToResize, false, \Imagick::FILTER_LANCZOS, 1);
    }

    /**
     * @param int $heightToResize
     */
    public function resizeToHeight($heightToResize)
    {
        $this->processor->resizeImage(false, $heightToResize, \Imagick::FILTER_LANCZOS, 1);
    }
}
