<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportApiDirectoryNotReadableException;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportFileNameNotFoundInRequestBodyException;
use LizardsAndPumpkins\Import\CatalogImport;

class CatalogImportApiV1PutRequestHandler extends ApiRequestHandler
{
    /**
     * @var CatalogImport
     */
    private $catalogImport;

    /**
     * @var string
     */
    private $importDirectoryPath;

    /**
     * @var Logger
     */
    private $logger;

    private function __construct(CatalogImport $catalogImport, string $importDirectoryPath, Logger $logger)
    {
        $this->catalogImport = $catalogImport;
        $this->importDirectoryPath = $importDirectoryPath;
        $this->logger = $logger;
    }

    public static function create(
        CatalogImport $catalogImport,
        string $importDirectoryPath,
        Logger $logger
    ) : CatalogImportApiV1PutRequestHandler {
        if (!is_readable($importDirectoryPath)) {
            throw new CatalogImportApiDirectoryNotReadableException(
                sprintf('%s is not readable.', $importDirectoryPath)
            );
        }

        return new self($catalogImport, $importDirectoryPath, $logger);
    }

    final public function canProcess(HttpRequest $request) : bool
    {
        return HttpRequest::METHOD_PUT === $request->getMethod();
    }

    final protected function getResponse(HttpRequest $request) : HttpResponse
    {
        $headers = [];
        $body = '';

        return GenericHttpResponse::create($body, $headers, HttpResponse::STATUS_ACCEPTED);
    }

    final protected function processRequest(HttpRequest $request)
    {
        $filePath = $this->getValidImportFilePathFromRequest($request);
        $this->catalogImport->importFile($filePath);
    }

    private function getValidImportFilePathFromRequest(HttpRequest $request) : string
    {
        return $this->importDirectoryPath . '/' . $this->getImportFileNameFromRequest($request);
    }

    private function getImportFileNameFromRequest(HttpRequest $request) : string
    {
        $requestArguments = json_decode($request->getRawBody(), true);

        if (!is_array($requestArguments) || !isset($requestArguments['fileName']) || !$requestArguments['fileName']) {
            throw new CatalogImportFileNameNotFoundInRequestBodyException(
                'Import file name is not found in request body.'
            );
        }

        return $requestArguments['fileName'];
    }
}
