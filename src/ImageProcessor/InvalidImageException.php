<?php

namespace Brera\ImageProcessor;

class InvalidImageException extends \Exception
{
    /**
     * @var string
     */
    private $imagePath;

    /**
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * @param string $imagePath
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    }
}
