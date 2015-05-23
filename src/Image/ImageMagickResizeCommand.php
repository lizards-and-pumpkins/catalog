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
     * @param string $imageStream
     * @return string
     * @throws InvalidImageStreamException
     */
    public function execute($imageStream)
    {
        $this->validateImageDimensions();

        $imagick = new \Imagick();

        try {
            $imagick->readImageBlob($imageStream);
        } catch (\ImagickException $e) {
            throw new InvalidImageStreamException($e->getMessage());
        }

        $imagick->resizeImage($this->width, $this->height, \Imagick::FILTER_LANCZOS, 1);

        return $imagick->getImageBlob();
    }
}
