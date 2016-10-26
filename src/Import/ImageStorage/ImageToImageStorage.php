<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\FileStorage\FileToFileStorage;

interface ImageToImageStorage extends FileToFileStorage
{
    public function url(Image $image, Context $context) : string;
}
