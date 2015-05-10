<?php

namespace Brera\ImageProcessor;

interface ImageProcessor
{
    /**
     * @param string $imagePath
     * @return void
     */
    public function setImage($imagePath);

    /**
     * @return string
     */
    public function getImage();

    /**
     * @param string $filename
     * @return boolean
     */
    public function saveAsFile($filename);

    /**
     * @param int $widthToResize
     */
    public function resizeToWidth($widthToResize);

    /**
     * @param int $heightToResize
     */
    public function resizeToHeight($heightToResize);

    /**
     * @param int $widthToResize
     * @param int $heightToResize
     */
    public function resize($widthToResize, $heightToResize);

    /**
     * @param int $widthToResize
     * @param int $heightToResize
     */
    public function resizeToBestFit($widthToResize, $heightToResize);
}
