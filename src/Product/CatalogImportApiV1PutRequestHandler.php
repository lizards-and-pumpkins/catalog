<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Image\UpdateImageCommand;
use Brera\Logger;
use Brera\Queue\Queue;
use Brera\Utils\XPathParser;

class CatalogImportApiV1PutRequestHandler extends ApiRequestHandler
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
     * @var ProductSourceBuilder
     */
    private $productSourceBuilder;

    /**
     * @var ProductListingMetaInfoSourceBuilder
     */
    private $productListingMetaInfoSourceBuilder;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Queue $commandQueue
     * @param string $importDirectoryPath
     * @param ProductSourceBuilder $productSourceBuilder
     * @param ProductListingMetaInfoSourceBuilder $productListingMetaInfoSourceBuilder
     * @param Logger $logger
     */
    private function __construct(
        Queue $commandQueue,
        $importDirectoryPath,
        ProductSourceBuilder $productSourceBuilder,
        ProductListingMetaInfoSourceBuilder $productListingMetaInfoSourceBuilder,
        Logger $logger
    ) {
        $this->commandQueue = $commandQueue;
        $this->importDirectoryPath = $importDirectoryPath;
        $this->productSourceBuilder = $productSourceBuilder;
        $this->productListingMetaInfoSourceBuilder = $productListingMetaInfoSourceBuilder;
        $this->logger = $logger;
    }

    /**
     * @param Queue $commandQueue
     * @param string $importDirectoryPath
     * @param ProductSourceBuilder $productSourceBuilder
     * @param ProductListingMetaInfoSourceBuilder $productListingMetaInfoSourceBuilder
     * @param Logger $logger
     * @return CatalogImportApiV1PutRequestHandler
     */
    public static function create(
        Queue $commandQueue,
        $importDirectoryPath,
        ProductSourceBuilder $productSourceBuilder,
        ProductListingMetaInfoSourceBuilder $productListingMetaInfoSourceBuilder,
        Logger $logger
    ) {
        if (!is_readable($importDirectoryPath)) {
            throw new CatalogImportDirectoryNotReadableException(sprintf('%s is not readable.', $importDirectoryPath));
        }

        return new self(
            $commandQueue,
            $importDirectoryPath,
            $productSourceBuilder,
            $productListingMetaInfoSourceBuilder,
            $logger
        );
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
        $xml = $this->getImportFileContents($request);

        $productNodesXml = (new XPathParser($xml))->getXmlNodesRawXmlArrayByXPath('//catalog/products/product');
        foreach ($productNodesXml as $productXml) {
            try {
                $productSource = $this->productSourceBuilder->createProductSourceFromXml($productXml);
                $this->commandQueue->add(new UpdateProductCommand($productSource));
            } catch (\Exception $exception) {
                $skuString = (new XPathParser($productXml))->getXmlNodesArrayByXPath('//@sku')[0]['value'];
                $productId = ProductId::fromString($skuString);
                $loggerMessage = new ProductImportFailedMessage($productId, $exception);
                $this->logger->log($loggerMessage);
            }
        }

        $listingNodesXml = (new XPathParser($xml))->getXmlNodesRawXmlArrayByXPath('//catalog/listings/listing');
        foreach ($listingNodesXml as $listingXml) {
            $productListingMetaInfoSource = $this->productListingMetaInfoSourceBuilder
                ->createProductListingMetaInfoSourceFromXml($listingXml);
            $this->commandQueue->add(new UpdateProductListingCommand($productListingMetaInfoSource));
        }

        $imageNodes = (new XPathParser($xml))->getXmlNodesArrayByXPath(
            '//catalog/products/product/attributes/image/file'
        );
        foreach ($imageNodes as $imageNode) {
            $this->commandQueue->add(new UpdateImageCommand($imageNode['value']));
        }
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
