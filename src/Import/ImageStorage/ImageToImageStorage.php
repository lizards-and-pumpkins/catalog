<?php

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\FileStorage\FileToFileStorage;

interface ImageToImageStorage extends FileToFileStorage
{
    /**
     * @param Image $image
     * @param Context $context
     * @return string
     */
    public function url(Image $image, Context $context);
}
