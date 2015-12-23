<?php

namespace LizardsAndPumpkins\Utils\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Utils\FileStorage\FileToFileStorage;

interface ImageToImageStorage extends FileToFileStorage
{
    /**
     * @param Image $image
     * @param Context $context
     * @return string
     */
    public function url(Image $image, Context $context);
}
