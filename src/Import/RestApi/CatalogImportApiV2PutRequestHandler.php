<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Import\ImportCatalogCommand;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportApiDirectoryIsNotDirectoryException;
use LizardsAndPumpkins\Import\RestApi\Exception\DataVersionNotFoundInRequestBodyException;
use LizardsAndPumpkins\Import\RestApi\Exception\InvalidDataVersionTypeException;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportApiDirectoryNotReadableException;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportFileNameNotFoundInRequestBodyException;

class CatalogImportApiV2PutRequestHandler implements HttpRequestHandler
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

    public function __construct(string $importDirectoryPath, CommandQueue $commandQueue, Logger $logger)
    {
        $this->validateImportDirectoryPath($importDirectoryPath);

        $this->importDirectoryPath = $importDirectoryPath;
        $this->commandQueue = $commandQueue;
        $this->logger = $logger;
    }

    private function validateImportDirectoryPath(string $importDirectoryPath): void
    {
        if (! is_readable($importDirectoryPath)) {
            throw new CatalogImportApiDirectoryNotReadableException(
                sprintf('The API catalog import directory "%s" is not readable.', $importDirectoryPath)
            );
        }
        if (! is_dir($importDirectoryPath)) {
            throw new CatalogImportApiDirectoryIsNotDirectoryException(
                sprintf('The API catalog import directory "%s" is not a directory', $importDirectoryPath)
            );
        }
    }

    final public function canProcess(HttpRequest $request): bool
    {
        return HttpRequest::METHOD_PUT === $request->getMethod();
    }

    final public function process(HttpRequest $request): HttpResponse
    {
        $filePath = $this->getValidImportFilePathFromRequest($request);
        $dataVersion = $this->createDataVersion($request);
        $this->commandQueue->add(new ImportCatalogCommand($dataVersion, $filePath));

        return GenericHttpResponse::create($body = '', $headers = [], HttpResponse::STATUS_ACCEPTED);
    }

    private function getValidImportFilePathFromRequest(HttpRequest $request): string
    {
        return $this->importDirectoryPath . '/' . $this->getImportFileNameFromRequest($request);
    }

    private function getImportFileNameFromRequest(HttpRequest $request): string
    {
        $requestArguments = json_decode($request->getRawBody(), true);

        if (! $this->hasArgument($requestArguments, 'fileName')) {
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

        if (! $this->hasArgument($requestArguments, 'dataVersion')) {
            throw new DataVersionNotFoundInRequestBodyException(
                'The catalog import data version is not found in request body.'
            );
        }

        if (! in_array(typeof($requestArguments['dataVersion']), ['string', 'integer', 'double'])) {
            throw new InvalidDataVersionTypeException(sprintf(
                'Data version is expected to be string, integer or float, "%s" is given.',
                typeof($requestArguments['dataVersion'])
            ));
        }

        return (string) $requestArguments['dataVersion'];
    }

    private function hasArgument($requestArguments, string $argument): bool
    {
        return is_array($requestArguments) && isset($requestArguments[$argument]) && $requestArguments[$argument];
    }
}
