<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Image\AddImageCommand;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductListingMetaInfoBuilder;
use LizardsAndPumpkins\Product\ProductSourceBuilder;
use LizardsAndPumpkins\Product\UpdateProductCommand;
use LizardsAndPumpkins\Product\AddProductListingCommand;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportFileDoesNotExistException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportFileNotReadableException;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Utils\UuidGenerator;
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
    private $productListingMetaInfoBuilder;

    /**
     * @var Queue
     */
    private $eventQueue;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        Queue $commandQueue,
        ProductSourceBuilder $productSourceBuilder,
        ProductListingMetaInfoBuilder $productListingMetaInfoBuilder,
        Queue $eventQueue,
        Logger $logger
    ) {
        $this->commandQueue = $commandQueue;
        $this->productSourceBuilder = $productSourceBuilder;
        $this->productListingMetaInfoBuilder = $productListingMetaInfoBuilder;
        $this->eventQueue = $eventQueue;
        $this->logger = $logger;
    }

    /**
     * @param string $importFilePath
     */
    public function importFile($importFilePath)
    {
        $version = DataVersion::fromVersionString('-1'); // UuidGenerator::getUuid()
        $this->validateImportFilePath($importFilePath);
        $parser = CatalogXmlParser::fromFilePath($importFilePath);
        $parser->registerProductSourceCallback($this->createClosureForMethod('processProductXml'));
        $parser->registerListingCallback($this->createClosureForMethod('processListingXml'));
        $imageDirectoryPath = dirname($importFilePath) . '/product-images';
        $parser->registerProductImageCallback($this->createClosureForProductImageXml($imageDirectoryPath));
        $parser->parse();
        $this->eventQueue->add(new CatalogWasImportedDomainEvent($version));
    }

    /**
     * @param string $importFilePath
     */
    private function validateImportFilePath($importFilePath)
    {
        if (!file_exists($importFilePath)) {
            throw new CatalogImportFileDoesNotExistException(
                sprintf('Catalog import file not found: "%s"', $importFilePath)
            );
        }
        if (!is_readable($importFilePath)) {
            throw new CatalogImportFileNotReadableException(
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
     * @param string $imageDirectoryPath
     * @return \Closure
     */
    private function createClosureForProductImageXml($imageDirectoryPath)
    {
        return function (...$args) use ($imageDirectoryPath) {
            return $this->processProductImageXml($imageDirectoryPath, ...$args);
        };
    }

    /**
     * @param string $productXml
     */
    private function processProductXml($productXml)
    {
        try {
            $productSource = $this->productSourceBuilder->createProductSourceFromXml($productXml);
            // todo: add version to command
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
        $productListingMetaInfo = $this->productListingMetaInfoBuilder
            ->createProductListingMetaInfoFromXml($listingXml);
        // todo: add version to command
        $this->commandQueue->add(new AddProductListingCommand($productListingMetaInfo));
    }

    /**
     * @param string $imageDirectoryPath
     * @param string $productImageXml
     */
    private function processProductImageXml($imageDirectoryPath, $productImageXml)
    {
        $fileNode = (new XPathParser($productImageXml))->getXmlNodesArrayByXPath('/image/file')[0];
        // todo: add version to command
        $this->commandQueue->add(new AddImageCommand($imageDirectoryPath . '/' . $fileNode['value']));
    }
}
