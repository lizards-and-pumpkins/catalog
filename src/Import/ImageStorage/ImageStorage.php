<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\FileStorage\FileStorage;
use LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri;

interface ImageStorage extends FileStorage
{
    public function getUrl(StorageAgnosticFileUri $identifier, Context $context) : HttpUrl;
}
