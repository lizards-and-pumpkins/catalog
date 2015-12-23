<?php

namespace LizardsAndPumpkins\Utils\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Utils\FileStorage\FileStorage;
use LizardsAndPumpkins\Utils\FileStorage\StorageAgnosticFileUri;

interface ImageStorage extends FileStorage
{
    /**
     * @param StorageAgnosticFileUri $identifier
     * @param Context $context
     * @return HttpUrl
     */
    public function getUrl(StorageAgnosticFileUri $identifier, Context $context);
}
