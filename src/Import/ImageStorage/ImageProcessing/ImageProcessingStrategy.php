<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing;

interface ImageProcessingStrategy
{
    public function processBinaryImageData(string $binaryImageData) : string;
}
