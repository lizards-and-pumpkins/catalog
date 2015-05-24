<?php

namespace Brera\Image;

class ImageMagickResizeCommand implements ImageProcessorCommand
{
    use ResizeCommandTrait;

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
     * @throws InvalidBinaryImageDataException
     */
    public function execute($binaryImageData)
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
