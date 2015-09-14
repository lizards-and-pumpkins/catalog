<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Log\Logger;
use Brera\Product\Exception\CatalogImportApiDirectoryNotReadableException;
use Brera\Product\Exception\CatalogImportFileNameNotFoundInRequestBodyException;
use Brera\Projection\Catalog\Import\CatalogImport;

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

    /**
     * @param CatalogImport $catalogImport
     * @param string $importDirectoryPath
     * @param Logger $logger
     */
    private function __construct(CatalogImport $catalogImport, $importDirectoryPath, Logger $logger)
    {
        $this->catalogImport = $catalogImport;
        $this->importDirectoryPath = $importDirectoryPath;
        $this->logger = $logger;
    }

    /**
     * @param CatalogImport $catalogImport
     * @param string $importDirectoryPath
     * @param Logger $logger
     * @return CatalogImportApiV1PutRequestHandler
     */
    public static function create(CatalogImport $catalogImport, $importDirectoryPath, Logger $logger)
    {
        if (!is_readable($importDirectoryPath)) {
            throw new CatalogImportApiDirectoryNotReadableException(
                sprintf('%s is not readable.', $importDirectoryPath)
            );
        }

        return new self($catalogImport, $importDirectoryPath, $logger);
    }

    /**
     * @param HttpRequest $request
     * @return bool
     */
    final public function canProcess(HttpRequest $request)
    {
        return HttpRequest::METHOD_PUT === $request->getMethod();
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    final protected function getResponseBody(HttpRequest $request)
    {
        return json_encode('OK');
    }

    protected function processRequest(HttpRequest $request)
    {
        $filePath = $this->getValidImportFilePathFromRequest($request);
        $this->catalogImport->importFile($filePath);
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getValidImportFilePathFromRequest(HttpRequest $request)
    {
        return $this->importDirectoryPath . '/' . $this->getImportFileNameFromRequest($request);
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getImportFileNameFromRequest(HttpRequest $request)
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
