<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing;

class ImageProcessorCollection
{
    /**
     * @var ImageProcessor[]
     */
    private $processors = [];

    public function add(ImageProcessor $processor)
    {
        $this->processors[] = $processor;
    }

    public function process(string $imageFilePath)
    {
        foreach ($this->processors as $processor) {
            $processor->process($imageFilePath);
        }
    }
}
