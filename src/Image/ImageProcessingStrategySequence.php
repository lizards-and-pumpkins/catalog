<?php

namespace LizardsAndPumpkins\Image;

class ImageProcessingStrategySequence implements ImageProcessingStrategy
{
    /**
     * @var ImageProcessingStrategy[]
     */
    private $strategies = [];

    public function add(ImageProcessingStrategy $strategy)
    {
        $this->strategies[] = $strategy;
    }

    /**
     * @param string $imageBinaryData
     * @return string
     */
    public function processBinaryImageData($imageBinaryData)
    {
        return array_reduce($this->strategies, function ($carryImageBinaryData, ImageProcessingStrategy $strategy) {
            return $strategy->processBinaryImageData($carryImageBinaryData);
        }, $imageBinaryData);
    }
}
