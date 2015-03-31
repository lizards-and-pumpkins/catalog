<?php
namespace Brera\ImageProcessor;

class ImageMagick implements ImageProcessor
{
    private $processor;

    /**
     * @param string $imagePath
     * @throws InvalidImageException
     */
    public function __construct($imagePath)
    {
        if (!$this->isValidImage($imagePath)) {
            $exception = new InvalidImageException();
            $exception->setImagePath($imagePath);
            throw $exception;
        }

        $this->processor = new \Imagick($imagePath);
    }

    /**
     * @param string $imagePath
     * @return bool
     */
    private function isValidImage($imagePath)
    {
        if (!is_readable($imagePath)) {
            return false;
        }

        return true;
    }

    public function saveAsFile($path)
    {
        $this->processor->writeImage($path);
    }
}
