<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileDoesNotExistException;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileNotReadableException;
use LizardsAndPumpkins\Import\Product\Image\ProductImageImportCallbackFailureMessage;
use LizardsAndPumpkins\Import\Product\ProductImportCallbackFailureMessage;
use LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Import\Product\QueueImportCommands;
use LizardsAndPumpkins\Import\XmlParser\CatalogXmlParser;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\ProductListing\Import\ProductListingBuilder;

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
     * @var DomainEventQueue
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
        QueueImportCommands $queueImportCommands,
        ProductXmlToProductBuilderLocator $productXmlToProductBuilder,
        ProductListingBuilder $productListingBuilder,
        DomainEventQueue $eventQueue,
        ContextSource $contextSource,
        Logger $logger
    )
    {
        $this->queueImportCommands = $queueImportCommands;
        $this->productXmlToProductBuilder = $productXmlToProductBuilder;
        $this->productListingBuilder = $productListingBuilder;
        $this->eventQueue = $eventQueue;
        $this->contextSource = $contextSource;
        $this->logger = $logger;
    }

    public function importFile(string $importFilePath, DataVersion $dataVersion)
    {
        $this->dataVersion = $dataVersion;
        $this->validateImportFilePath($importFilePath);
        $parser = CatalogXmlParser::fromFilePath($importFilePath, $this->logger);
        $parser->registerProductCallback($this->createClosureForMethod('processProductXml'));
        $parser->registerListingCallback($this->createClosureForMethod('processListingXml'));
        $this->imageDirectoryPath = dirname($importFilePath) . '/product-images';
        $parser->parse();
        $this->addCatalogImportedDomainEvent();
    }

    private function validateImportFilePath(string $importFilePath)
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

    private function createClosureForMethod(string $methodName): \Closure
    {
        return function (...$args) use ($methodName) {
            return $this->{$methodName}(...$args);
        };
    }

    private function processProductXml(string $productXml)
    {
        try {
            $this->addProductsAndProductImagesToQueue($productXml, $this->dataVersion);
        } catch (\Exception $exceptionWillInterruptFurtherProcessingOfThisProduct) {
            $this->logger->log(new ProductImportCallbackFailureMessage(
                $exceptionWillInterruptFurtherProcessingOfThisProduct,
                $productXml
            ));
            throw $exceptionWillInterruptFurtherProcessingOfThisProduct;
        }
    }

    public function addProductsAndProductImagesToQueue(string $productXml, DataVersion $dataVersion)
    {
        $this->dataVersion = $dataVersion;
        $productBuilder = $this->productXmlToProductBuilder->createProductBuilderFromXml($productXml);
        $contexts = $this->contextSource->getAllAvailableContextsWithVersionApplied($this->dataVersion);
        every($contexts, function (Context $context) use ($productBuilder, $productXml) {
            if ($productBuilder->isAvailableForContext($context)) {
                $product = $productBuilder->getProductForContext($context);
                $this->queueImportCommands->forProduct($product);
                $this->processImagesInProductXml($productXml);
            }
        });
    }

    private function processImagesInProductXml(string $productXml)
    {
        $imageNodes = (new XPathParser($productXml))->getXmlNodesRawXmlArrayByXPath('/product/images/image');
        every($imageNodes, function ($productImageXml) {
            $this->processProductImageXml($productImageXml);
        });
    }

    private function processProductImageXml(string $productImageXml)
    {
        try {
            $fileNode = (new XPathParser($productImageXml))->getXmlNodesArrayByXPath('/image/file')[0];
            $imageFilePath = $this->imageDirectoryPath . '/' . $fileNode['value'];
            $this->queueImportCommands->forImage($imageFilePath, $this->dataVersion);
        } catch (\Exception $exception) {
            $this->logger->log(new ProductImageImportCallbackFailureMessage($exception, $productImageXml));
        }
    }

    private function processListingXml(string $listingXml)
    {
        try {
            $productListing = $this->productListingBuilder
                ->createProductListingFromXml($listingXml, $this->dataVersion);
            $this->queueImportCommands->forListing($productListing);
        } catch (\Exception $exception) {
            $this->logger->log(new CatalogListingImportCallbackFailureMessage($exception, $listingXml));
        }
    }

    private function addCatalogImportedDomainEvent()
    {
        $this->eventQueue->add(new CatalogWasImportedDomainEvent($this->dataVersion));
    }
}
