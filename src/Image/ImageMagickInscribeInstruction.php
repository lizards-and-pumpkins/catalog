<?php

namespace Brera\Image;

class ImageMagickInscribeInstruction implements ImageProcessorInstruction
{
    use ResizeInstructionTrait;

    /**
     * @var string
     */
    private $backgroundColor;

    /**
     * @param int $width
     * @param int $height
     * @param string $backgroundColor
     */
    public function __construct($width, $height, $backgroundColor)
    {
        $this->width = $width;
        $this->height = $height;
        $this->backgroundColor = $backgroundColor;
    }

    /**
     * @param string $binaryImageData
     * @return string
     */
    public function execute($binaryImageData)
    {
        $this->validateImageDimensions();
        $this->validateBackgroundColor();

        $image = new \Imagick();

        try {
            $image->readImageBlob($binaryImageData);
        } catch (\ImagickException $e) {
            throw new InvalidBinaryImageDataException($e->getMessage());
        }

        $image->resizeImage($this->width, $this->height, \Imagick::FILTER_LANCZOS, 1, true);
        $canvas = $this->inscribeImageIntoCanvas($image);

        return $canvas->getImageBlob();
    }

    /**
     * @param \Imagick $image
     * @return \Imagick
     */
    private function inscribeImageIntoCanvas(\Imagick $image)
    {
        $dimensions = $image->getImageGeometry();
        $x = ($this->width - $dimensions['width']) / 2;
        $y = ($this->height - $dimensions['height']) / 2;

        $canvas = new \Imagick();
        $canvas->newImage($this->width, $this->height, $this->backgroundColor, $image->getImageFormat());
        $canvas->compositeImage($image, \Imagick::COMPOSITE_OVER, $x, $y);

        return $canvas;
    }

    /**
     * @throws InvalidColorException
     */
    private function validateBackgroundColor()
    {
        try {
            (new \ImagickPixel())->setColor($this->backgroundColor);
        } catch (\Exception $e) {
            if ($e instanceof \ImagickException || $e instanceof \ImagickPixelException) {
                throw new InvalidColorException($e->getMessage());
            }
        }
    }
}
