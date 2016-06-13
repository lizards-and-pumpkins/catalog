<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Product\Image\ProductImageImportCallbackFailureMessage;
use LizardsAndPumpkins\Import\Product\ProductImportCallbackFailureMessage;
use LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Import\Product\QueueImportCommands;
use LizardsAndPumpkins\Import\XmlParser\CatalogXmlParser;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\ProductListing\Import\ProductListingBuilder;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileDoesNotExistException;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileNotReadableException;

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
    ) {
        $this->queueImportCommands = $queueImportCommands;
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
        $this->addCatalogImportedDomainEvent();
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
        $contexts = $this->contextSource->getAllAvailableContextsWithVersion($this->dataVersion);
        every($contexts, function (Context $context) use ($productBuilder, $productXml) {
            if ($productBuilder->isAvailableForContext($context)) {
                $product = $productBuilder->getProductForContext($context);
                $this->queueImportCommands->forProduct($product);
                $this->processImagesInProductXml($productXml);
            }
        });
    }

    /**
     * @param string $productXml
     */
    private function processImagesInProductXml($productXml)
    {
        $imageNodes = (new XPathParser($productXml))->getXmlNodesRawXmlArrayByXPath('/product/images/image');
        every($imageNodes, function ($productImageXml) {
            $this->processProductImageXml($productImageXml);
        });
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

    private function addCatalogImportedDomainEvent()
    {
        $this->eventQueue->add(new CatalogWasImportedDomainEvent($this->dataVersion));
    }
}
