<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\BaseUrl\IntegrationTestFixedBaseUrlBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Context\Country\IntegrationTestContextCountry;
use LizardsAndPumpkins\Context\IntegrationTestContextSource;
use LizardsAndPumpkins\Context\Locale\IntegrationTestContextLocale;
use LizardsAndPumpkins\Context\Website\IntegrationTestContextWebsite;
use LizardsAndPumpkins\Context\Website\IntegrationTestUrlToWebsiteMap;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\DataPool\KeyValueStore\InMemoryKeyValueStore;
use LizardsAndPumpkins\Import\FileStorage\FileStorageReader;
use LizardsAndPumpkins\Import\FileStorage\FileStorageWriter;
use LizardsAndPumpkins\Import\Tax\TaxableCountries;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue\InMemoryQueue;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductsPerPage;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField;
use LizardsAndPumpkins\DataPool\SearchEngine\InMemorySearchEngine;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\InMemoryUrlKeyStore;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessor;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategySequence;
use LizardsAndPumpkins\Logging\InMemoryLogger;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\View\ProductImageFileLocator;
use LizardsAndPumpkins\Import\Product\View\IntegrationTestProductViewLocator;
use LizardsAndPumpkins\Import\Product\View\ProductViewLocator;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Tax\IntegrationTestTaxServiceLocator;
use LizardsAndPumpkins\Import\ImageStorage\FilesystemImageStorage;
use LizardsAndPumpkins\Import\ImageStorage\ImageStorage;
use LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageReader;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageWriter;

class IntegrationTestFactory implements Factory, MessageQueueFactory
{
    use FactoryTrait;

    const PROCESSED_IMAGES_DIR = 'lizards-and-pumpkins/processed-images';

    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var DomainEventQueue
     */
    private $eventQueue;

    /**
     * @var Queue
     */
    private $eventMessageQueue;

    /**
     * @var CommandQueue
     */
    private $commandQueue;

    /**
     * @var Queue
     */
    private $commandMessageQueue;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var UrlKeyStore
     */
    private $urlKeyStore;

    /**
     * @var ProductsPerPage
     */
    private $memoizedProductsPerPageConfig;
    
    /**
     * @return string[]
     */
    public function getSearchableAttributeCodes() : array
    {
        return ['name', 'category', 'brand'];
    }

    /**
     * @param Context $context
     * @return FacetFilterRequestField[]
     */
    public function getProductListingFacetFilterRequestFields(Context $context) : array
    {
        return $this->getCommonFacetFilterRequestFields();
    }

    /**
     * @param Context $context
     * @return FacetFilterRequestField[]
     */
    public function getProductSearchFacetFilterRequestFields(Context $context) : array
    {
        return array_merge(
            $this->getCommonFacetFilterRequestFields(),
            [new FacetFilterRequestSimpleField(AttributeCode::fromString('category'))]
        );
    }

    /**
     * @return string[]
     */
    public function getFacetFilterRequestFieldCodesForSearchDocuments() : array
    {
        return array_map(function (FacetFilterRequestField $field) {
            return (string) $field->getAttributeCode();
        }, $this->getCommonFacetFilterRequestFields());
    }

    /**
     * @return FacetFilterRequestField[]
     */
    private function getCommonFacetFilterRequestFields() : array
    {
        return [
            new FacetFilterRequestSimpleField(AttributeCode::fromString('gender')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('brand')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('price')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('color'))
        ];
    }

    /**
     * @return string[]
     */
    public function getSortableAttributeCodes() : array
    {
        return ['backorders', 'stock_qty', 'series'];
    }

    public function createKeyValueStore() : InMemoryKeyValueStore
    {
        return new InMemoryKeyValueStore();
    }

    public function setCommandMessageQueue(Queue $commandQueue)
    {
        $this->commandMessageQueue = $commandQueue;
    }

    public function getCommandQueue() : CommandQueue
    {
        if (null === $this->commandQueue) {
            $this->commandQueue = $this->createCommandQueue();
        }
        return $this->commandQueue;
    }

    public function createCommandQueue() : CommandQueue
    {
        return new CommandQueue($this->getCommandMessageQueue());
    }

    public function getCommandMessageQueue() : Queue
    {
        if (null === $this->commandMessageQueue) {
            $this->commandMessageQueue = $this->createCommandMessageQueue();
        }
        return $this->commandMessageQueue;
    }

    public function createCommandMessageQueue() : Queue
    {
        return new InMemoryQueue();
    }

    public function setEventMessageQueue(Queue $eventQueue)
    {
        $this->eventMessageQueue = $eventQueue;
    }

    public function getEventQueue() : DomainEventQueue
    {
        if (null === $this->eventQueue) {
            $this->eventQueue = $this->createEventQueue();
        }
        return $this->eventQueue;
    }

    public function createEventQueue() : DomainEventQueue
    {
        return new DomainEventQueue($this->getEventMessageQueue());
    }

    public function getEventMessageQueue() : Queue
    {
        if (null === $this->eventMessageQueue) {
            $this->eventMessageQueue = $this->createEventMessageQueue();
        }
        return $this->eventMessageQueue;
    }

    public function createEventMessageQueue() : Queue
    {
        return new InMemoryQueue();
    }

    public function createLogger() : InMemoryLogger
    {
        return new InMemoryLogger();
    }

    public function createSearchEngine() : InMemorySearchEngine
    {
        return new InMemorySearchEngine(
            $this->getMasterFactory()->getFacetFieldTransformationRegistry(),
            ...$this->getMasterFactory()->getSearchableAttributeCodes()
        );
    }

    public function createFacetFieldTransformationRegistry() : FacetFieldTransformationRegistry
    {
        return new FacetFieldTransformationRegistry;
    }

    public function createUrlKeyStore() : UrlKeyStore
    {
        return new InMemoryUrlKeyStore();
    }

    public function createImageProcessorCollection() : ImageProcessorCollection
    {
        $processorCollection = new ImageProcessorCollection();
        $processorCollection->add($this->getMasterFactory()->createImageProcessor());

        return $processorCollection;
    }

    public function createImageProcessor() : ImageProcessor
    {
        $strategySequence = $this->getMasterFactory()->createImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createFileStorageWriter();
        
        $resultImageDir = $this->getMasterFactory()->getFileStorageBasePathConfig() . '/' . self::PROCESSED_IMAGES_DIR;
        
        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter, $resultImageDir);
    }

    public function createFileStorageReader() : FileStorageReader
    {
        return new LocalFilesystemStorageReader();
    }

    public function createFileStorageWriter() : FileStorageWriter
    {
        return new LocalFilesystemStorageWriter();
    }

    public function createBaseUrlBuilder() : BaseUrlBuilder
    {
        return new IntegrationTestFixedBaseUrlBuilder();
    }

    public function createAssetsBaseUrlBuilder() : BaseUrlBuilder
    {
        return new IntegrationTestFixedBaseUrlBuilder();
    }

    public function createImageProcessingStrategySequence() : ImageProcessingStrategySequence
    {
        return new ImageProcessingStrategySequence();
    }

    public function getKeyValueStore() : KeyValueStore
    {
        if (null === $this->keyValueStore) {
            $this->keyValueStore = $this->createKeyValueStore();
        }
        return $this->keyValueStore;
    }

    public function setKeyValueStore(KeyValueStore $keyValueStore)
    {
        $this->keyValueStore = $keyValueStore;
    }

    public function getSearchEngine() : SearchEngine
    {
        if (null === $this->searchEngine) {
            $this->searchEngine = $this->createSearchEngine();
        }
        return $this->searchEngine;
    }

    public function setSearchEngine(SearchEngine $searchEngine)
    {
        $this->searchEngine = $searchEngine;
    }

    public function getUrlKeyStore() : UrlKeyStore
    {
        if (null === $this->urlKeyStore) {
            $this->urlKeyStore = $this->createUrlKeyStore();
        }
        return $this->urlKeyStore;
    }

    public function setUrlKeyStore(UrlKeyStore $urlKeyStore)
    {
        $this->urlKeyStore = $urlKeyStore;
    }

    public function getFileStorageBasePathConfig() : string
    {
        return sys_get_temp_dir();
    }

    /**
     * @return SortBy[]
     */
    public function getProductListingAvailableSortBy() : array
    {
        return [$this->getProductListingDefaultSortBy()];
    }

    public function getProductListingDefaultSortBy() : SortBy
    {
        return new SortBy(AttributeCode::fromString('name'), SortDirection::create(SortDirection::ASC));
    }

    /**
     * @return SortBy[]
     */
    public function getProductSearchAvailableSortBy() : array
    {
        return [$this->getProductSearchDefaultSortBy()];
    }

    public function getProductSearchDefaultSortBy() : SortBy
    {
        return new SortBy(AttributeCode::fromString('stock_qty'), SortDirection::create(SortDirection::DESC));
    }

    public function getProductsPerPageConfig() : ProductsPerPage
    {
        if (null === $this->memoizedProductsPerPageConfig) {
            $numbersOfProductsPerPage = [9, 12, 18];
            $selectedNumberOfProductsPerPage = 9;

            $this->memoizedProductsPerPageConfig = ProductsPerPage::create(
                $numbersOfProductsPerPage,
                $selectedNumberOfProductsPerPage
            );
        }

        return $this->memoizedProductsPerPageConfig;
    }

    public function createTaxableCountries() : TaxableCountries
    {
        return new IntegrationTestTaxableCountries();
    }

    public function createTaxServiceLocator() : IntegrationTestTaxServiceLocator
    {
        return new IntegrationTestTaxServiceLocator();
    }

    public function createProductViewLocator() : ProductViewLocator
    {
        return new IntegrationTestProductViewLocator($this->getMasterFactory()->createProductImageFileLocator());
    }

    public function createGlobalProductListingCriteria() : SearchCriteria
    {
        return new SearchCriterionGreaterThan('stock_qty', 0);
    }

    public function createProductImageFileLocator() : ProductImageFileLocator
    {
        return new IntegrationTestProductImageFileLocator($this->getMasterFactory()->createImageStorage());
    }

    public function createImageStorage() : ImageStorage
    {
        return new FilesystemImageStorage(
            $this->getMasterFactory()->createFilesystemFileStorage(),
            $this->getMasterFactory()->createMediaBaseUrlBuilder(),
            $this->getMasterFactory()->getMediaBaseDirectoryConfig()
        );
    }

    public function createSearchFieldToRequestParamMap() : SearchFieldToRequestParamMap
    {
        $facetFieldToQueryParameterMap = [];
        $queryParameterToFacetFieldMap = [];
        return new SearchFieldToRequestParamMap($facetFieldToQueryParameterMap, $queryParameterToFacetFieldMap);
    }

    public function createThemeLocator() : ThemeLocator
    {
        return new ThemeLocator(__DIR__ . '/../fixture');
    }

    public function createContextSource() : ContextSource
    {
        return new IntegrationTestContextSource($this->getMasterFactory()->createContextBuilder());
    }

    public function createLocaleContextPartBuilder() : ContextPartBuilder
    {
        return new IntegrationTestContextLocale();
    }

    public function createCountryContextPartBuilder() : ContextPartBuilder
    {
        return new IntegrationTestContextCountry();
    }

    public function createWebsiteContextPartBuilder() : ContextPartBuilder
    {
        return new IntegrationTestContextWebsite();
    }
    
    public function createUrlToWebsiteMap() : UrlToWebsiteMap
    {
        return new IntegrationTestUrlToWebsiteMap();
    }
    
    public function getMaxAllowedProductsPerSearchResultsPage() : int
    {
        return 120;
    }

    public function getDefaultNumberOfProductsPerSearchResultsPage()
    {
        return 60;
    }
}
