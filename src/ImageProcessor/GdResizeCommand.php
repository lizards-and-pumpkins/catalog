<?php

namespace Brera\ImageProcessor;

class GdResizeCommand implements ImageProcessorCommand
{
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

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
     * @param string $base64EncodedImageStream
     * @return string
     */
    public function execute($base64EncodedImageStream)
    {
        $this->validateImageDimensions();

        $imageStream = base64_decode($base64EncodedImageStream);
        $imageInfo = $this->getImageInfo($imageStream);

        $this->validateImageType($imageInfo);

        $image = imagecreatefromstring($imageStream);
        $resultImage = imagecreatetruecolor($this->width, $this->height);

        imagecopyresampled($resultImage, $image, 0, 0, 0, 0, $this->width, $this->height, $imageInfo[0], $imageInfo[1]);

        $outputImageStream = $this->getOutputImageStream($resultImage, $imageInfo);

        return base64_encode($outputImageStream);
    }

    /**
     * @throws InvalidImageDimensionException
     */
    private function validateImageDimensions()
    {
        if (!is_int($this->width)) {
            throw new InvalidImageDimensionException(
                sprintf('Expected integer as image width, got %s.', gettype($this->width))
            );
        }

        if (!is_int($this->height)) {
            throw new InvalidImageDimensionException(
                sprintf('Expected integer as image height, got %s.', gettype($this->height))
            );
        }

        if ($this->width <= 0) {
            throw new InvalidImageDimensionException(
                sprintf('Image width should be greater then zero, got %s.', $this->width)
            );
        }

        if ($this->height <= 0) {
            throw new InvalidImageDimensionException(
                sprintf('Image height should be greater then zero, got %s.', $this->height)
            );
        }
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
