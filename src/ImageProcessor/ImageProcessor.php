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
     * @return boolean
     */
    public function resizeToWidth($widthToResize);

    /**
     * @param $heightToResize
     * @return boolean
     */
    public function resizeToHeight($heightToResize);
}
