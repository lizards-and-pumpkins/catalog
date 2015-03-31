<?php

namespace Brera\ImageProcessor;

interface ImageProcessor
{
    /**
     * @param string $filename
     * @return boolean
     */
    public function saveAsFile($filename);

    /**
     * @param $widthToResize
     */
    public function resizeToWidth($widthToResize);

    /**
     * @param $heightToResize
     */
    public function resizeToHeight($heightToResize);
}
