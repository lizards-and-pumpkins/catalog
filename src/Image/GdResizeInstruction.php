<?php

namespace Brera\Image;

class GdResizeInstruction implements ImageProcessorInstruction
{
    use ResizeInstructionTrait;

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
    public function execute($binaryImageData)
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
     * @throws InvalidBinaryImageDataException
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
     * @param mixed[] $imageInfo
     * @return string
     */
    private function getSaveFunctionName(array $imageInfo)
    {
        return 'image' . strtolower(preg_replace('/.*\//', '', $imageInfo['mime']));
    }

    /**
     * @param mixed[] $imageInfo
     * @throws InvalidBinaryImageDataException
     */
    private function validateImageType(array $imageInfo)
    {
        $saveFunctionName = $this->getSaveFunctionName($imageInfo);

        if (!function_exists($saveFunctionName)) {
            throw new InvalidBinaryImageDataException();
        }
    }

    /**
     * @param resource $image
     * @param mixed[] $imageInfo
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
