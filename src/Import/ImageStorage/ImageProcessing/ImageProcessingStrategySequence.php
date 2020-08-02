<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing;

class ImageProcessingStrategySequence implements ImageProcessingStrategy
{
    /**
     * @var ImageProcessingStrategy[]
     */
    private $strategies = [];

    public function add(ImageProcessingStrategy $strategy): void
    {
        $this->strategies[] = $strategy;
    }

    public function processBinaryImageData(string $imageBinaryData) : string
    {
        return array_reduce($this->strategies, function ($carryImageBinaryData, ImageProcessingStrategy $strategy) {
            return $strategy->processBinaryImageData($carryImageBinaryData);
        }, $imageBinaryData);
    }
}
