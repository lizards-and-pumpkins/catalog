<?php


namespace LizardsAndPumpkins\Utils\ImageStorage;

use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Utils\FileStorage\File;

interface Image extends File
{
    /**
     * @return HttpUrl
     */
    public function getUrl();
}
