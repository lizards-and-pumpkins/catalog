<?php

namespace LizardsAndPumpkins\Image;

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

    /**
     * @param string $imageFilePath
     */
    public function process($imageFilePath)
    {
        foreach ($this->processors as $processor) {
            $processor->process($imageFilePath);
        }
    }
}
