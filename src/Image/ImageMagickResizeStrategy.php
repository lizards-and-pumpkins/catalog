<?php

namespace Brera\Image;

class ImageMagickResizeStrategy implements ImageProcessingStrategy
{
    use ResizeStrategyTrait;

    /**
     * @param int $width
     * @param int $height
     */
    public function __construct($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @param string $binaryImageData
     * @return string
     */
    public function processBinaryImageData($binaryImageData)
    {
        $this->validateImageDimensions();

        $imagick = new \Imagick();

        try {
            $imagick->readImageBlob($binaryImageData);
        } catch (\ImagickException $e) {
            throw new InvalidBinaryImageDataException($e->getMessage());
        }

        $imagick->resizeImage($this->width, $this->height, \Imagick::FILTER_LANCZOS, 1);

        return $imagick->getImageBlob();
    }
}
