<?php

namespace LizardsAndPumpkins\Image;

interface ImageProcessingStrategy
{
    /**
     * @param string $binaryImageData
     * @return string
     */
    public function processBinaryImageData($binaryImageData);
}
