<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Import\FileStorage\FileStorageReader;
use LizardsAndPumpkins\Import\FileStorage\FileStorageWriter;
use LizardsAndPumpkins\Import\Tax\TaxableCountries;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\MessageQueueFactory;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductsPerPage;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategy;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessor;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Import\Product\View\ProductImageFileLocator;
use LizardsAndPumpkins\Import\Product\View\ProductViewLocator;
use LizardsAndPumpkins\Import\Tax\TaxServiceLocator;
use LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;

class UnitTestFactory implements Factory, MessageQueueFactory
{
    use FactoryTrait;
    
    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var Queue
     */
    private $eventQueue;

    /**
     * @var Queue
     */
    private $commandQueue;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var UrlKeyStore
     */
    private $urlKeyStore;

    /**
     * @var \PHPUnit_Framework_TestCase
     */
    private $testCase;

    public function __construct(\PHPUnit_Framework_TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    /**
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createMock($className)
    {
        return (new \PHPUnit_Framework_MockObject_MockBuilder($this->testCase, $className))
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
    }

    /**
     * @return string[]
     */
    public function getSearchableAttributeCodes()
    {
        return [];
    }

    /**
     * @return FacetFiltersToIncludeInResult
     */
    public function createProductListingFacetFiltersToIncludeInResult()
    {
        return new FacetFiltersToIncludeInResult();
    }

    /**
     * @return KeyValueStore
     */
    public function createKeyValueStore()
    {
        return $this->createMock(KeyValueStore::class);
    }

    /**
     * @return DomainEventQueue
     */
    public function createEventQueue()
    {
        return $this->createMock(DomainEventQueue::class);
    }

    /**
     * @return Queue
     */
    public function createEventMessageQueue()
    {
        return $this->createMock(Queue::class);
    }

    /**
     * @return CommandQueue
     */
    public function createCommandQueue()
    {
        return $this->createMock(CommandQueue::class);
    }

    /**
     * @return Queue
     */
    public function createCommandMessageQueue()
    {
        return $this->createMock(Queue::class);
    }

    /**
     * @return Logger
     */
    public function createLogger()
    {
        return $this->createMock(Logger::class);
    }

    /**
     * @return SearchEngine
     */
    public function createSearchEngine()
    {
        return $this->createMock(SearchEngine::class);
    }

    /**
     * @return UrlKeyStore
     */
    public function createUrlKeyStore()
    {
        return $this->createMock(UrlKeyStore::class);
    }

    /**
     * @return ImageProcessorCollection
     */
    public function createImageProcessorCollection()
    {
        return $this->createMock(ImageProcessorCollection::class);
    }

    /**
     * @return ImageProcessor
     */
    public function createImageProcessor()
    {
        return $this->createMock(ImageProcessor::class);
    }

    /**
     * @return FileStorageReader
     */
    public function createFileStorageReader()
    {
        return $this->createMock(FileStorageReader::class);
    }

    /**
     * @return FileStorageWriter
     */
    public function createFileStorageWriter()
    {
        return $this->createMock(FileStorageWriter::class);
    }

    /**
     * @return BaseUrlBuilder
     */
    public function createBaseUrlBuilder()
    {
        return $this->createMock(BaseUrlBuilder::class);
    }

    /**
     * @return ImageProcessingStrategy
     */
    public function createImageProcessingStrategySequence()
    {
        return $this->createMock(ImageProcessingStrategy::class);
    }

    /**
     * @return KeyValueStore
     */
    public function getKeyValueStore()
    {
        if (null === $this->keyValueStore) {
            $this->keyValueStore = $this->createKeyValueStore();
        }
        return $this->keyValueStore;
    }

    /**
     * @return DomainEventQueue
     */
    public function getEventQueue()
    {
        if (null === $this->eventQueue) {
            $this->eventQueue = $this->createEventQueue();
        }
        return $this->eventQueue;
    }

    /**
     * @return CommandQueue
     */
    public function getCommandQueue()
    {
        if (null === $this->commandQueue) {
            $this->commandQueue = $this->createCommandQueue();
        }
        return $this->commandQueue;
    }

    /**
     * @return SearchEngine
     */
    public function getSearchEngine()
    {
        if (null === $this->searchEngine) {
            $this->searchEngine = $this->createSearchEngine();
        }
        return $this->searchEngine;
    }

    /**
     * @return UrlKeyStore
     */
    public function getUrlKeyStore()
    {
        if (null === $this->urlKeyStore) {
            $this->urlKeyStore = $this->createUrlKeyStore();
        }
        return $this->urlKeyStore;
    }

    /**
     * @return SortOrderConfig[]
     */
    public function getProductListingSortOrderConfig()
    {
        return [$this->createMock(SortOrderConfig::class)];
    }

    /**
     * @return SortOrderConfig[]
     */
    public function getProductSearchSortOrderConfig()
    {
        return [$this->createMock(SortOrderConfig::class)];
    }

    /**
     * @return SortOrderConfig
     */
    public function getProductSearchAutosuggestionSortOrderConfig()
    {
        return $this->createMock(SortOrderConfig::class);
    }

    /**
     * @return string
     */
    public function getFileStorageBasePathConfig()
    {
        return '';
    }

    /**
     * @return FacetFieldTransformationRegistry
     */
    public function createFacetFieldTransformationRegistry()
    {
        return $this->createMock(FacetFieldTransformationRegistry::class);
    }

    /**
     * @return TaxableCountries
     */
    public function createTaxableCountries()
    {
        return $this->createMock(TaxableCountries::class);
    }

    /**
     * @return ProductViewLocator
     */
    public function createProductViewLocator()
    {
        return $this->createMock(ProductViewLocator::class);
    }

    /**
     * @return TaxServiceLocator
     */
    public function createTaxServiceLocator()
    {
        return $this->createMock(TaxServiceLocator::class);
    }

    /**
     * @return SearchCriteria
     */
    public function createGlobalProductListingCriteria()
    {
        return $this->createMock(SearchCriteria::class);
    }

    /**
     * @return ProductImageFileLocator
     */
    public function createProductImageFileLocator()
    {
        return $this->createMock(ProductImageFileLocator::class);
    }

    /**
     * @return ProductsPerPage
     */
    public function getProductsPerPageConfig()
    {
        return $this->createMock(ProductsPerPage::class);
    }

    /**
     * @return string[]
     */
    public function getAdditionalAttributesForSearchIndex()
    {
        return [];
    }

    /**
     * @param Context $context
     * @return FacetFilterRequestField[]
     */
    public function getProductListingFacetFilterRequestFields(Context $context)
    {
        return $this->getCommonFacetFilterRequestFields();
    }

    /**
     * @param Context $context
     * @return FacetFilterRequestField[]
     */
    public function getProductSearchFacetFilterRequestFields(Context $context)
    {
        return $this->getCommonFacetFilterRequestFields();
    }

    /**
     * @return string[]
     */
    public function getFacetFilterRequestFieldCodesForSearchDocuments()
    {
        return [];
    }

    /**
     * @return FacetFilterRequestField[]
     */
    private function getCommonFacetFilterRequestFields()
    {
        return [];
    }

    /**
     * @return SearchFieldToRequestParamMap
     */
    public function createSearchFieldToRequestParamMap()
    {
        return $this->createMock(SearchFieldToRequestParamMap::class);
    }

    /**
     * @return ThemeLocator
     */
    public function createThemeLocator()
    {
        return $this->createMock(ThemeLocator::class);
    }

    /**
     * @return ContextSource
     */
    public function createContextSource()
    {
        return $this->createMock(ContextSource::class);
    }

    /**
     * @return ContextPartBuilder
     */
    public function createLocaleContextPartBuilder()
    {
        return $this->createMock(ContextPartBuilder::class);
    }

    /**
     * @return ContextPartBuilder
     */
    public function createCountryContextPartBuilder()
    {
        return $this->createMock(ContextPartBuilder::class);
    }

    /**
     * @return ContextPartBuilder
     */
    public function createWebsiteContextPartBuilder()
    {
        return $this->createMock(ContextPartBuilder::class);
    }
}
