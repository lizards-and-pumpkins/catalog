<?php

namespace Brera\Image;

use Brera\Command;

class UpdateImageCommand implements Command
{
    /**
     * @var string
     */
    private $imageFileName;

    /**
     * @param string $imageFileName
     */
    public function __construct($imageFileName)
    {
        $this->imageFileName = $imageFileName;
    }

    /**
     * @return string
     */
    public function getImageFileName()
    {
        return $this->imageFileName;
    }
}
