<?php

namespace Brera\Image;

interface ImageProcessorInstruction
{
    /**
     * @param string $binaryImageData
     * @return string
     */
    public function execute($binaryImageData);
}
