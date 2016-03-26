<?php


namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\FileStorage\File;

interface Image extends File
{
    /**
     * @param Context $context
     * @return HttpUrl
     */
    public function getUrl(Context $context);
}
