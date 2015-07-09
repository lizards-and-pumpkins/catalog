<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Queue\Queue;

class CatalogImportApiRequestHandler extends ApiRequestHandler
{
    /**
     * @var Queue
     */
    private $domainEventQueue;

    /**
     * @var string
     */
    private $importDirectoryPath;

    /**
     * @param Queue $domainEventQueue
     * @param string $importDirectoryPath
     */
    private function __construct(Queue $domainEventQueue, $importDirectoryPath)
    {
        $this->domainEventQueue = $domainEventQueue;
        $this->importDirectoryPath = $importDirectoryPath;
    }

    /**
     * @param Queue $domainEventQueue
     * @param string $importDirectoryPath
     * @return CatalogImportApiRequestHandler
     * @throws CatalogImportDirectoryNotReadableException
     */
    public static function create(Queue $domainEventQueue, $importDirectoryPath)
    {
        if (!is_readable($importDirectoryPath)) {
            throw new CatalogImportDirectoryNotReadableException;
        }

        return new self($domainEventQueue, $importDirectoryPath);
    }

    /**
     * @return bool
     */
    final public function canProcess()
    {
        return true;
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    final protected function getResponseBody(HttpRequest $request)
    {
        $importFileContents = $this->getImportFileContents($request);

        $catalogImportDomainEvent = new CatalogImportDomainEvent($importFileContents);
        $this->domainEventQueue->add($catalogImportDomainEvent);

        return json_encode('OK');
    }

    /**
     * @param HttpRequest $request
     * @return string
     * @throws CatalogImportFileNotReadableException
     */
    protected function getImportFileContents(HttpRequest $request)
    {
        $filePath = $this->importDirectoryPath . '/' . $this->getImportFileNameFromRequest($request);

        if (!is_readable($filePath)) {
            throw new CatalogImportFileNotReadableException;
        }

        return file_get_contents($filePath);
    }

    /**
     * @param HttpRequest $request
     * @return string
     * @throws CatalogImportFileNameNotFoundInRequestBodyException
     */
    protected function getImportFileNameFromRequest(HttpRequest $request)
    {
        $requestArguments = json_decode($request->getRawBody(), true);

        if (!is_array($requestArguments) || !isset($requestArguments['fileName']) || !$requestArguments['fileName']) {
            throw new CatalogImportFileNameNotFoundInRequestBodyException;
        }

        return $requestArguments['fileName'];
    }
}
