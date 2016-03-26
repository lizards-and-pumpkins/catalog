<?php

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Gd;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidBinaryImageDataException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategy;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ResizeStrategyTrait;

class GdResizeStrategy implements ImageProcessingStrategy
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

        $imageInfo = $this->getImageInfo($binaryImageData);

        $this->validateImageType($imageInfo);

        $image = imagecreatefromstring($binaryImageData);
        $resultImage = imagecreatetruecolor($this->width, $this->height);

        imagecopyresampled($resultImage, $image, 0, 0, 0, 0, $this->width, $this->height, $imageInfo[0], $imageInfo[1]);

        return $this->getBinaryImageOutput($resultImage, $imageInfo);
    }

    /**
     * @param string $binaryImageData
     * @return mixed[]
     */
    private function getImageInfo($binaryImageData)
    {
        $imageInfo = @getimagesizefromstring($binaryImageData);

        if (false === $imageInfo) {
            throw new InvalidBinaryImageDataException();
        }

        return $imageInfo;
    }

    /**
     * @param string[] $imageInfo
     * @return string
     */
    private function getSaveFunctionName(array $imageInfo)
    {
        return 'image' . strtolower(preg_replace('/.*\//', '', $imageInfo['mime']));
    }

    /**
     * @param string[] $imageInfo
     */
    private function validateImageType(array $imageInfo)
    {
        $saveFunctionName = $this->getSaveFunctionName($imageInfo);

        if (!function_exists($saveFunctionName)) {
            throw new InvalidBinaryImageDataException(sprintf('MIME type "%s" is not supported.', $imageInfo['mime']));
        }
    }

    /**
     * @param resource $image
     * @param string[] $imageInfo
     * @return string
     */
    private function getBinaryImageOutput($image, array $imageInfo)
    {
        $saveFunctionName = $this->getSaveFunctionName($imageInfo);

        ob_start();
        $saveFunctionName($image);
        $binaryImageData = ob_get_contents();
        ob_end_clean();

        return $binaryImageData;
    }
}
