<?php

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing;

interface ImageProcessingStrategy
{
    /**
     * @param string $binaryImageData
     * @return string
     */
    public function processBinaryImageData($binaryImageData);
}
