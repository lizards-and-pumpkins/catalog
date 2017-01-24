<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\HttpRequest;

class CatalogImportApiV1PutRequestHandler extends CatalogImportApiV2PutRequestHandler
{
    final protected function createDataVersion(HttpRequest $request): DataVersion
    {
        return DataVersion::fromVersionString('-1');
    }
}
