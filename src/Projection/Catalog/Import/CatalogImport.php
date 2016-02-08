<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Product\ProductListingBuilder;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportFileDoesNotExistException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportFileNotReadableException;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Utils\XPathParser;

class CatalogImport
{
    /**
     * @var ProductXmlToProductBuilderLocator
     */
    private $productXmlToProductBuilder;

    /**
     * @var ProductListingBuilder
     */
    private $productListingBuilder;

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

    /**
     * @var string
     */
    private $imageDirectoryPath;

    /**
     * @var QueueImportCommands
     */
    private $queueImportCommands;

    public function __construct(
        QueueImportCommands $quueImportCommands,
        ProductXmlToProductBuilderLocator $productXmlToProductBuilder,
        ProductListingBuilder $productListingBuilder,
        Queue $eventQueue,
        ContextSource $contextSource,
        Logger $logger
    ) {
        $this->queueImportCommands = $quueImportCommands;
        $this->productXmlToProductBuilder = $productXmlToProductBuilder;
        $this->productListingBuilder = $productListingBuilder;
        $this->eventQueue = $eventQueue;
        $this->contextSource = $contextSource;
        $this->logger = $logger;
    }

    /**
     * @param string $importFilePath
     */
    public function importFile($importFilePath)
    {
        // Todo: once all projectors support using the passed data version of context data sets, use the UUID version
        $this->dataVersion = DataVersion::fromVersionString('-1'); // UuidGenerator::getUuid()
        $this->validateImportFilePath($importFilePath);
        $parser = CatalogXmlParser::fromFilePath($importFilePath, $this->logger);
        $parser->registerProductCallback($this->createClosureForMethod('processProductXml'));
        $parser->registerListingCallback($this->createClosureForMethod('processListingXml'));
        $this->imageDirectoryPath = dirname($importFilePath) . '/product-images';
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
     * @param string $productXml
     */
    private function processProductXml($productXml)
    {
        try {
            $this->addProductsAndProductImagesToQueue($productXml);
        } catch (\Exception $exceptionWillInterruptFurtherProcessingOfThisProduct) {
            $this->logger->log(new ProductImportCallbackFailureMessage(
                $exceptionWillInterruptFurtherProcessingOfThisProduct,
                $productXml
            ));
            throw $exceptionWillInterruptFurtherProcessingOfThisProduct;
        }
    }

    /**
     * @param string $productXml
     */
    public function addProductsAndProductImagesToQueue($productXml)
    {
        $productBuilder = $this->productXmlToProductBuilder->createProductBuilderFromXml($productXml);
        array_map(function (Context $context) use ($productBuilder, $productXml) {
            if ($productBuilder->isAvailableForContext($context)) {
                $product = $productBuilder->getProductForContext($context);
                $this->queueImportCommands->forProduct($product);
                $this->processImagesInProductXml($productXml);
            }
        }, $this->contextSource->getAllAvailableContextsWithVersion($this->dataVersion));
    }


    /**
     * @param string $productXml
     */
    private function processImagesInProductXml($productXml)
    {
        $imageNodes = (new XPathParser($productXml))->getXmlNodesRawXmlArrayByXPath('/product/images/image');
        // Suppress PHP printing warnings despite a wrapping try/catch block (what a fine PHP Bug).
        // Note: the exception still gets raised as intended.
        @array_map([$this, 'processProductImageXml'], $imageNodes);
    }

    /**
     * @param string $productImageXml
     */
    private function processProductImageXml($productImageXml)
    {
        try {
            $fileNode = (new XPathParser($productImageXml))->getXmlNodesArrayByXPath('/image/file')[0];
            $imageFilePath = $this->imageDirectoryPath . '/' . $fileNode['value'];
            $this->queueImportCommands->forImage($imageFilePath, $this->dataVersion);
        } catch (\Exception $exception) {
            $this->logger->log(new ProductImageImportCallbackFailureMessage($exception, $productImageXml));
        }
    }

    /**
     * @param string $listingXml
     */
    private function processListingXml($listingXml)
    {
        try {
            $productListing = $this->productListingBuilder
                ->createProductListingFromXml($listingXml, $this->dataVersion);
            $this->queueImportCommands->forListing($productListing);
        } catch (\Exception $exception) {
            $this->logger->log(new CatalogListingImportCallbackFailureMessage($exception, $listingXml));
        }
    }
}
