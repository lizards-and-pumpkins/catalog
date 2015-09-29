<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Image\AddImageCommand;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductListingCriteriaBuilder;
use LizardsAndPumpkins\Product\ProductBuilder;
use LizardsAndPumpkins\Product\ProductXmlToProductBuilder;
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
     * @var ProductXmlToProductBuilder
     */
    private $productXmlToProductBuilder;

    /**
     * @var ProductListingCriteriaBuilder
     */
    private $productListingCriteriaBuilder;

    /**
     * @var Queue
     */
    private $eventQueue;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var DataVersion
     */
    private $dataVersion;

    public function __construct(
        Queue $commandQueue,
        ProductXmlToProductBuilder $productXmlToProductBuilder,
        ProductListingCriteriaBuilder $productListingCriteriaBuilder,
        Queue $eventQueue,
        ContextSource $contextSource,
        Logger $logger
    ) {
        $this->commandQueue = $commandQueue;
        $this->productXmlToProductBuilder = $productXmlToProductBuilder;
        $this->productListingCriteriaBuilder = $productListingCriteriaBuilder;
        $this->eventQueue = $eventQueue;
        $this->contextSource = $contextSource;
        $this->logger = $logger;
    }

    /**
     * @param string $importFilePath
     */
    public function importFile($importFilePath)
    {
        $this->dataVersion = DataVersion::fromVersionString('-1'); // UuidGenerator::getUuid()
        $this->validateImportFilePath($importFilePath);
        $parser = CatalogXmlParser::fromFilePath($importFilePath);
        $parser->registerProductCallback($this->createClosureForMethod('processProductXml'));
        $parser->registerListingCallback($this->createClosureForMethod('processListingXml'));
        $imageDirectoryPath = dirname($importFilePath) . '/product-images';
        $parser->registerProductImageCallback($this->createClosureForProductImageXml($imageDirectoryPath));
        $parser->parse();
        $this->eventQueue->add(new CatalogWasImportedDomainEvent($this->dataVersion));
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
            $this->addProductsFromBuilderToQueue($this->productXmlToProductBuilder->createProductBuilderFromXml($productXml));
        } catch (\Exception $exception) {
            $this->logProductImportException($exception, $productXml);
        }
    }

    public function addProductsFromBuilderToQueue(ProductBuilder $productBuilder)
    {
        array_map(function (Context $context) use ($productBuilder) {
            $this->addCommandToQueue($productBuilder->getProductForContext($context));
        }, $this->contextSource->getAllAvailableContextsWithVersion($this->dataVersion));
    }

    private function addCommandToQueue(Product $product)
    {
        $this->commandQueue->add(new UpdateProductCommand($product));
    }

    /**
     * @param \Exception $exception
     * @param string $productXml
     */
    private function logProductImportException(\Exception $exception, $productXml)
    {
        $skuString = (new XPathParser($productXml))->getXmlNodesArrayByXPath('//@sku')[0]['value'];
        $productId = ProductId::fromString($skuString);
        $loggerMessage = new ProductImportFailedMessage($productId, $exception);
        $this->logger->log($loggerMessage);
    }

    /**
     * @param string $listingXml
     */
    private function processListingXml($listingXml)
    {
        $productListingCriteria = $this->productListingCriteriaBuilder
            ->createProductListingCriteriaFromXml($listingXml, $this->dataVersion);
        $this->commandQueue->add(new AddProductListingCommand($productListingCriteria));
    }

    /**
     * @param string $imageDirectoryPath
     * @param string $productImageXml
     */
    private function processProductImageXml($imageDirectoryPath, $productImageXml)
    {
        $fileNode = (new XPathParser($productImageXml))->getXmlNodesArrayByXPath('/image/file')[0];
        $imageFilePath = $imageDirectoryPath . '/' . $fileNode['value'];
        $this->commandQueue->add(new AddImageCommand($imageFilePath, $this->dataVersion));
    }
}
