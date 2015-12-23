<?php


namespace LizardsAndPumpkins\Utils\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Utils\FileStorage\File;

interface Image extends File
{
    /**
     * @param Context $context
     * @return HttpUrl
     */
    public function getUrl(Context $context);
}
