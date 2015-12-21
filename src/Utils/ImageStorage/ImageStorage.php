<?php


namespace LizardsAndPumpkins\Utils\ImageStorage;

use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Utils\FileStorage\FileStorage;
use LizardsAndPumpkins\Utils\FileStorage\StorageAgnosticFileUri;

interface ImageStorage extends FileStorage
{
    /**
     * @param StorageAgnosticFileUri $identifier
     * @return HttpUrl
     */
    public function getUrl(StorageAgnosticFileUri $identifier);
}
