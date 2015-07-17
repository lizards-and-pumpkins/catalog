<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Queue\Queue;
use Brera\Utils\Directory;
use Brera\Utils\XPathParser;

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

    /**
     * @var ProductStockQuantitySourceBuilder
     */
    private $productStockQuantitySourceBuilder;

    private function __construct(
        Queue $commandQueue,
        Directory $importDirectory,
        ProductStockQuantitySourceBuilder $productStockQuantitySourceBuilder
    ) {
        $this->commandQueue = $commandQueue;
        $this->importDirectory = $importDirectory;
        $this->productStockQuantitySourceBuilder = $productStockQuantitySourceBuilder;
    }

    /**
     * @param Queue $commandQueue
     * @param Directory $importDirectory
     * @param ProductStockQuantitySourceBuilder $productStockQuantitySourceBuilder
     * @return CatalogImportApiRequestHandler
     * @throws CatalogImportDirectoryNotReadableException
     */
    public static function create(
        Queue $commandQueue,
        Directory $importDirectory,
        ProductStockQuantitySourceBuilder $productStockQuantitySourceBuilder
    ) {
        if (!$importDirectory->isReadable()) {
            throw new CatalogImportDirectoryNotReadableException(
                sprintf('%s is not readable.', $importDirectory->getPath())
            );
        }

        return new self($commandQueue, $importDirectory, $productStockQuantitySourceBuilder);
    }

    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        return HttpRequest::METHOD_PUT === $request->getMethod();
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    protected function getResponseBody(HttpRequest $request)
    {
        $importFileContents = $this->getImportFileContents($request);

        $productStockQuantitySourceArray = [];
        $stockNodesXml = (new XPathParser($importFileContents))->getXmlNodesRawXmlArrayByXPath('/*/stock');
        foreach ($stockNodesXml as $xml) {
            $productStockQuantitySourceArray[] = $this->productStockQuantitySourceBuilder->createFromXml($xml);
        }

        $command = new UpdateMultipleProductStockQuantityCommand($productStockQuantitySourceArray);
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
