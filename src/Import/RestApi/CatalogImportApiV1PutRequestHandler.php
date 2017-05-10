<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;

class CatalogImportApiV1PutRequestHandler extends CatalogImportApiV2PutRequestHandler
{
    /**
     * @var DataVersion
     */
    private $dataVersion;

    public function __construct(
        string $importDirectoryPath,
        CommandQueue $commandQueue,
        Logger $logger,
        DataVersion $dataVersion
    ) {
        parent::__construct($importDirectoryPath, $commandQueue, $logger);
        $this->dataVersion = $dataVersion;
    }

    final protected function createDataVersion(HttpRequest $request): DataVersion
    {
        return $this->dataVersion;
    }
}
