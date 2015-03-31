<?php
namespace Brera\ImageProcessor;

interface ImageProcessor
{
    /**
     * @param string $filename
     * @return boolean
     */
    public function saveAsFile($filename);
}
