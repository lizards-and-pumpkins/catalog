<?php

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\FileStorage\FileStorage;
use LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri;

interface ImageStorage extends FileStorage
{
    /**
     * @param StorageAgnosticFileUri $identifier
     * @param Context $context
     * @return HttpUrl
     */
    public function getUrl(StorageAgnosticFileUri $identifier, Context $context);
}
