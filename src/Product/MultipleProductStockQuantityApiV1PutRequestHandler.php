<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Api\ApiRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Product\Exception\CatalogImportApiDirectoryNotReadableException;
use LizardsAndPumpkins\Product\Exception\CatalogImportFileNameNotFoundInRequestBodyException;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Utils\Directory;
use LizardsAndPumpkins\Utils\XPathParser;

class MultipleProductStockQuantityApiV1PutRequestHandler extends ApiRequestHandler
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
     * @return CatalogImportApiV1PutRequestHandler
     */
    public static function create(
        Queue $commandQueue,
        Directory $importDirectory,
        ProductStockQuantitySourceBuilder $productStockQuantitySourceBuilder
    ) {
        if (!$importDirectory->isReadable()) {
            throw new CatalogImportApiDirectoryNotReadableException(
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
        return json_encode('OK');
    }

    protected function processRequest(HttpRequest $request)
    {
        $importFileContents = $this->getImportFileContents($request);

        $productStockQuantitySourceArray = [];
        $stockNodesXml = (new XPathParser($importFileContents))->getXmlNodesRawXmlArrayByXPath('/*/stock');
        foreach ($stockNodesXml as $xml) {
            $productStockQuantitySourceArray[] = $this->productStockQuantitySourceBuilder->createFromXml($xml);
        }

        $command = new UpdateMultipleProductStockQuantityCommand($productStockQuantitySourceArray);
        $this->commandQueue->add($command);
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getImportFileContents(HttpRequest $request)
    {
        $filePath = $this->importDirectory->getPath() . '/' . $this->getImportFileNameFromRequest($request);

        if (!is_readable($filePath)) {
            $message = sprintf('The catalog import file "%s" is not readable.', $filePath);
            throw new CatalogImportFileNotReadableException($message);
        }

        return file_get_contents($filePath);
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
