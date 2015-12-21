<?php

namespace LizardsAndPumpkins\Utils\ImageStorage;

use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Utils\FileStorage\FileToFileStorage;

interface ImageToImageStorage extends FileToFileStorage
{
    /**
     * @param Image $image
     * @return string
     */
    public function url(Image $image);
}
