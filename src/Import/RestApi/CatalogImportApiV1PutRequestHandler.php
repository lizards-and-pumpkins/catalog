<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;

class CatalogImportApiV1PutRequestHandler extends CatalogImportApiV2PutRequestHandler
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    public function __construct(
        string $importDirectoryPath,
        CommandQueue $commandQueue,
        Logger $logger,
        DataPoolReader $dataPoolReader
    ) {
        parent::__construct($importDirectoryPath, $commandQueue, $logger);
        $this->dataPoolReader = $dataPoolReader;
    }

    final protected function createDataVersion(HttpRequest $request): DataVersion
    {
        return DataVersion::fromVersionString($this->dataPoolReader->getCurrentDataVersion());
    }
}
