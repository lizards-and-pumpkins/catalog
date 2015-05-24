<?php

namespace Brera\Image;

interface ImageProcessorCommand
{
    /**
     * @param string $binaryImageData
     * @return string
     */
    public function execute($binaryImageData);
}
