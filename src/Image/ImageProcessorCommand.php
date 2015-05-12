<?php

namespace Brera\Image;

interface ImageProcessorCommand
{
    /**
     * @param string $imageStream
     * @return string
     */
    public function execute($imageStream);
}
