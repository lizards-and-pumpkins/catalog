<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\ImportCatalogCommand;
use LizardsAndPumpkins\Import\RestApi\Exception\DataVersionNotFoundInRequestBodyException;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportApiDirectoryNotReadableException;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportFileNameNotFoundInRequestBodyException;
use LizardsAndPumpkins\Import\CatalogImport;

class CatalogImportApiV2PutRequestHandler extends ApiRequestHandler
{
    /**
     * @var string
     */
    private $importDirectoryPath;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CommandQueue
     */
    private $commandQueue;

    private function __construct(string $importDirectoryPath, CommandQueue $commandQueue, Logger $logger)
    {
        $this->importDirectoryPath = $importDirectoryPath;
        $this->commandQueue = $commandQueue;
        $this->logger = $logger;
    }

    public static function create(
        string $importDirectoryPath,
        CommandQueue $commandQueue,
        Logger $logger
    ): ApiRequestHandler {
        if (!is_readable($importDirectoryPath)) {
            throw new CatalogImportApiDirectoryNotReadableException(
                sprintf('%s is not readable.', $importDirectoryPath)
            );
        }

        return new static($importDirectoryPath, $commandQueue, $logger);
    }

    final public function canProcess(HttpRequest $request): bool
    {
        return HttpRequest::METHOD_PUT === $request->getMethod();
    }

    final protected function getResponse(HttpRequest $request): HttpResponse
    {
        $headers = [];
        $body = '';

        return GenericHttpResponse::create($body, $headers, HttpResponse::STATUS_ACCEPTED);
    }

    final protected function processRequest(HttpRequest $request)
    {
        $filePath = $this->getValidImportFilePathFromRequest($request);
        $dataVersion = $this->createDataVersion($request);
        $this->commandQueue->add(new ImportCatalogCommand($dataVersion, $filePath));
    }

    private function getValidImportFilePathFromRequest(HttpRequest $request): string
    {
        return $this->importDirectoryPath . '/' . $this->getImportFileNameFromRequest($request);
    }

    private function getImportFileNameFromRequest(HttpRequest $request): string
    {
        $requestArguments = json_decode($request->getRawBody(), true);

        if (!is_array($requestArguments) || !isset($requestArguments['fileName']) || !$requestArguments['fileName']) {
            throw new CatalogImportFileNameNotFoundInRequestBodyException(
                'Import file name is not found in request body.'
            );
        }

        return $requestArguments['fileName'];
    }

    protected function createDataVersion(HttpRequest $request): DataVersion
    {
        $versionString = $this->getDataVersionFromRequest($request);

        return DataVersion::fromVersionString($versionString);
    }

    private function getDataVersionFromRequest(HttpRequest $request): string
    {
        $requestArguments = json_decode($request->getRawBody(), true);

        if (!is_array($requestArguments) ||
            !isset($requestArguments['dataVersion']) ||
            !$requestArguments['dataVersion']
        ) {
            throw new DataVersionNotFoundInRequestBodyException(
                'The catalog import data version is not found in request body.'
            );
        }

        return $requestArguments['dataVersion'];
    }
}
