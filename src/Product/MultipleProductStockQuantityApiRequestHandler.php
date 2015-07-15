<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Queue\Queue;
use Brera\Utils\Directory;

class MultipleProductStockQuantityApiRequestHandler extends ApiRequestHandler
{
    /**
     * @var Queue
     */
    private $commandQueue;

    /**
     * @var Directory
     */
    private $importDirectory;

    private function __construct(Queue $commandQueue, Directory $importDirectory)
    {
        $this->commandQueue = $commandQueue;
        $this->importDirectory = $importDirectory;
    }

    /**
     * @param Queue $commandQueue
     * @param Directory $importDirectory
     * @return CatalogImportApiRequestHandler
     * @throws CatalogImportDirectoryNotReadableException
     */
    public static function create(Queue $commandQueue, Directory $importDirectory)
    {
        if (!$importDirectory->isReadable()) {
            throw new CatalogImportDirectoryNotReadableException(
                sprintf('%s is not readable.', $importDirectory->getPath())
            );
        }

        return new self($commandQueue, $importDirectory);
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

        $command = new UpdateMultipleProductStockQuantityCommand($importFileContents);
        $this->commandQueue->add($command);

        return json_encode('OK');
    }

    /**
     * @param HttpRequest $request
     * @return string
     * @throws CatalogImportFileNotReadableException
     */
    private function getImportFileContents(HttpRequest $request)
    {
        $filePath = $this->importDirectory->getPath() . '/' . $this->getImportFileNameFromRequest($request);

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
