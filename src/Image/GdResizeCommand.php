<?php

namespace Brera\Image;

class GdResizeCommand implements ImageProcessorCommand
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
     */
    public function execute($imageStream)
    {
        $this->validateImageDimensions();

        $imageInfo = $this->getImageInfo($imageStream);

        $this->validateImageType($imageInfo);

        $image = imagecreatefromstring($imageStream);
        $resultImage = imagecreatetruecolor($this->width, $this->height);

        imagecopyresampled($resultImage, $image, 0, 0, 0, 0, $this->width, $this->height, $imageInfo[0], $imageInfo[1]);

        return $this->getOutputImageStream($resultImage, $imageInfo);
    }

    /**
     * @param string $imageStream
     * @return mixed[]
     * @throws InvalidImageStreamException
     */
    private function getImageInfo($imageStream)
    {
        $imageInfo = @getimagesizefromstring($imageStream);

        if (false === $imageInfo) {
            throw new InvalidImageStreamException();
        }

        return $imageInfo;
    }

    /**
     * @param mixed[] $imageInfo
     * @return string
     */
    private function getSaveFunctionName(array $imageInfo)
    {
        return 'image' . strtolower(preg_replace('/.*\//', '', $imageInfo['mime']));
    }

    /**
     * @param mixed[] $imageInfo
     * @throws InvalidImageStreamException
     */
    private function validateImageType(array $imageInfo)
    {
        $saveFunctionName = $this->getSaveFunctionName($imageInfo);

        if (!function_exists($saveFunctionName)) {
            throw new InvalidImageStreamException();
        }
    }

    /**
     * @param resource $image
     * @param mixed[] $imageInfo
     * @return string
     */
    private function getOutputImageStream($image, array $imageInfo)
    {
        $saveFunctionName = $this->getSaveFunctionName($imageInfo);

        ob_start();
        $saveFunctionName($image);
        $outputImageStream = ob_get_contents();
        ob_end_clean();

        return $outputImageStream;
    }
}
