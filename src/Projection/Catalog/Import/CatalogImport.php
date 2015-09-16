<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Image\UpdateImageCommand;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductListingMetaInfoBuilder;
use LizardsAndPumpkins\Product\ProductSourceBuilder;
use LizardsAndPumpkins\Product\UpdateProductCommand;
use LizardsAndPumpkins\Product\UpdateProductListingCommand;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Utils\XPathParser;

class CatalogImport
{
    /**
     * @var Queue
     */
    private $commandQueue;

    /**
     * @var ProductSourceBuilder
     */
    private $productSourceBuilder;

    /**
     * @var ProductListingMetaInfoBuilder
     */
    private $productListingMetaInfoSourceBuilder;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        Queue $commandQueue,
        ProductSourceBuilder $productSourceBuilder,
        ProductListingMetaInfoBuilder $productListingMetaInfoSourceBuilder,
        Logger $logger
    ) {

        $this->commandQueue = $commandQueue;
        $this->productSourceBuilder = $productSourceBuilder;
        $this->productListingMetaInfoSourceBuilder = $productListingMetaInfoSourceBuilder;
        $this->logger = $logger;
    }

    /**
     * @param string $importFilePath
     */
    public function importFile($importFilePath)
    {
        $this->validateImportFilePath($importFilePath);
        $parser = CatalogXmlParser::fromFilePath($importFilePath);
        $parser->registerProductSourceCallback($this->createClosureForMethod('processProductXml'));
        $parser->registerListingCallback($this->createClosureForMethod('processListingXml'));
        $parser->registerProductImageCallback($this->createClosureForMethod('processProductImageXml'));
        $parser->parse();
    }

    /**
     * @param string $importFilePath
     */
    private function validateImportFilePath($importFilePath)
    {
        if (!file_exists($importFilePath)) {
            throw new Exception\CatalogImportFileDoesNotExistException(
                sprintf('Catalog import file not found: "%s"', $importFilePath)
            );
        }
        if (!is_readable($importFilePath)) {
            throw new Exception\CatalogImportFileNotReadableException(
                sprintf('Catalog import file is not readable: "%s"', $importFilePath)
            );
        }
    }

    /**
     * @param string $methodName
     * @return \Closure
     */
    private function createClosureForMethod($methodName)
    {
        return function (...$args) use ($methodName) {
            return $this->{$methodName}(...$args);
        };
    }

    /**
     * @param string $productXml
     */
    private function processProductXml($productXml)
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
    private function processListingXml($listingXml)
    {
        $productListingMetaInfoSource = $this->productListingMetaInfoSourceBuilder
            ->createProductListingMetaInfoSourceFromXml($listingXml);
        $this->commandQueue->add(new UpdateProductListingCommand($productListingMetaInfoSource));
    }

    /**
     * @param string $productImageXml
     */
    private function processProductImageXml($productImageXml)
    {
        $fileNode = (new XPathParser($productImageXml))->getXmlNodesArrayByXPath('/image/file')[0];
        $this->commandQueue->add(new UpdateImageCommand($fileNode['value']));
    }
}
