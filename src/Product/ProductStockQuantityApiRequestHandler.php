<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Queue\Queue;

class ProductStockQuantityApiRequestHandler extends ApiRequestHandler
{
    /**
     * @var Queue
     */
    private $commandQueue;

    /**
     * @var string
     */
    private $importDirectoryPath;

    /**
     * @param Queue $commandQueue
     * @param string $importDirectoryPath
     */
    private function __construct(Queue $commandQueue, $importDirectoryPath)
    {
        $this->commandQueue = $commandQueue;
        $this->importDirectoryPath = $importDirectoryPath;
    }

    /**
     * @param Queue $commandQueue
     * @param string $importDirectoryPath
     * @return CatalogImportApiRequestHandler
     * @throws CatalogImportDirectoryNotReadableException
     */
    public static function create(Queue $commandQueue, $importDirectoryPath)
    {
        if (!is_readable($importDirectoryPath)) {
            throw new CatalogImportDirectoryNotReadableException(sprintf('%s is not readable.', $importDirectoryPath));
        }

        return new self($commandQueue, $importDirectoryPath);
    }

    /**
     * @return bool
     */
    public function canProcess()
    {
        return true;
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    protected function getResponseBody(HttpRequest $request)
    {
        $importFileContents = $this->getImportFileContents($request);

        $updateProductStockQuantityCommand = new UpdateProductStockQuantityCommand($importFileContents);
        $this->commandQueue->add($updateProductStockQuantityCommand);

        return json_encode('OK');
    }

    /**
     * @param HttpRequest $request
     * @return string
     * @throws CatalogImportFileNotReadableException
     */
    private function getImportFileContents(HttpRequest $request)
    {
        $filePath = $this->importDirectoryPath . '/' . $this->getImportFileNameFromRequest($request);

        if (!is_readable($filePath)) {
            throw new CatalogImportFileNotReadableException(sprintf('%s file is not readable.', $filePath));
        }

        return file_get_contents($filePath);
    }

    /**
     * @param HttpRequest $request
     * @return string
     * @throws CatalogImportFileNameNotFoundInRequestBodyException
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
