<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Image\UpdateImageCommand;
use Brera\Log\Logger;
use Brera\Projection\Catalog\Import\CatalogXmlParser;
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
        $filePath = $this->getValidImportFilePathFromRequest($request);
        $parser = CatalogXmlParser::fromFilePath($filePath);
        $parser->registerProductSourceCallback([$this, 'processProductXml']);
        $parser->registerListingCallback([$this, 'processListingXml']);
        $parser->registerProductImageCallback([$this, 'processProductImageXml']);
        $parser->parse();
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

    /**
     * @param string $productXml
     */
    public function processProductXml($productXml)
    {
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

    /**
     * @param string $listingXml
     */
    public function processListingXml($listingXml)
    {
        $productListingMetaInfoSource = $this->productListingMetaInfoSourceBuilder
            ->createProductListingMetaInfoSourceFromXml($listingXml);
        $this->commandQueue->add(new UpdateProductListingCommand($productListingMetaInfoSource));
    }

    /**
     * @param string $productImageXml
     */
    public function processProductImageXml($productImageXml)
    {
        $fileNode = (new XPathParser($productImageXml))->getXmlNodesArrayByXPath('/image/file')[0];
        $this->commandQueue->add(new UpdateImageCommand($fileNode['value']));
    }

    /**
     * @param string $filePath
     */
    private function validateImportFileIsReadable($filePath)
    {
        if (!is_readable($filePath)) {
            throw new CatalogImportFileNotReadableException(sprintf('%s file is not readable.', $filePath));
        }
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getValidImportFilePathFromRequest(HttpRequest $request)
    {
        $filePath = $this->importDirectoryPath . '/' . $this->getImportFileNameFromRequest($request);
        $this->validateImportFileIsReadable($filePath);
        return $filePath;
    }
}
