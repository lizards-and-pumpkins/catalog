<?php

namespace Brera\ImageProcessor;

class ImageMagickImageProcessor implements ImageProcessor
{
    /**
     * @var \Imagick
     */
    private $processor;

    /**
     * @var string
     */
    private $image;

    /**
     * @param string $imagePath
     */
    private function __construct($imagePath = null)
    {
        $this->image = $imagePath;
    }

    private function reset()
    {
        $this->processor = null;
    }

    /**
     * @param string $imagePath
     * @return ImageMagickImageProcessor
     */
    public static function fromFile($imagePath)
    {
        if (!is_readable($imagePath)) {
            throw new InvalidImageException(sprintf('"File "%s" doesn\'t exist or is not readable', $imagePath));
        }

        return new self($imagePath);
    }

    public static function fromNothing()
    {
        return new self();
    }

    /**
     * @param string $path
     * @return bool
     */
    public function saveAsFile($path)
    {
        $this->validatePath($path);

        try {
            return $this->getProcessor()->writeImage($path);
        } catch (\ImagickException $e) {
            throw new ImageSaveFailedException($e->getMessage());
        }

    }

    /**
     * @param int $widthToResize
     */
    public function resizeToWidth($widthToResize)
    {
        $this->getProcessor()->resizeImage($widthToResize, false, \Imagick::FILTER_LANCZOS, 1);
    }

    /**
     * @param int $heightToResize
     */
    public function resizeToHeight($heightToResize)
    {
        $this->getProcessor()->resizeImage(false, $heightToResize, \Imagick::FILTER_LANCZOS, 1);
    }

    /**
     * @param string $path
     * @throws InvalidImageException
     */
    private function validatePath($path)
    {
        if (is_scalar($path) && !is_string($path)) {
            throw new ImageSaveFailedException(sprintf('Image could not be saved, "%s" is not string.', $path));
        }

        if (!is_string($path)) {
            throw new ImageSaveFailedException('Image could not be saved, $path is no string.');
        }
    }

    /**
     * @param int $widthToResize
     * @param int $heightToResize
     */
    public function resize($widthToResize, $heightToResize)
    {
        $this->getProcessor()->resizeImage($widthToResize, $heightToResize, \Imagick::FILTER_LANCZOS, 1);
    }

    /**
     * @param int $widthToResize
     * @param int $heightToResize
     */
    public function resizeToBestFit($widthToResize, $heightToResize)
    {
        $this->getProcessor()->resizeImage($widthToResize, $heightToResize, \Imagick::FILTER_LANCZOS, 1, true);
    }

    /**
     * @return \Imagick
     */
    private function getProcessor()
    {
        if (!($this->processor instanceof \Imagick)) {
            $this->processor = new \Imagick($this->image);
        }

        return $this->processor;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }


    /**
     * @param string $imagePath
     * @return void
     */
    public function setImage($imagePath)
    {
        $this->image = $imagePath;
        $this->reset();
    }
}
