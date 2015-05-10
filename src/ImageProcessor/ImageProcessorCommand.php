<?php

namespace Brera\ImageProcessor;

interface ImageProcessorCommand
{
    /**
     * @param string $base64EncodedImageStream
     * @return string
     */
    public function execute($base64EncodedImageStream);
}
