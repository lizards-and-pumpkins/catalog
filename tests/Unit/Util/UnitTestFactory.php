<?php

declare(strict_types=1);

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
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
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
use LizardsAndPumpkins\RestApi\ApiRouter;
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

    private function createMock(string $className) : \PHPUnit_Framework_MockObject_MockObject
    {
        return (new \PHPUnit_Framework_MockObject_MockBuilder($this->testCase, $className))
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
    }

    /**
     * @return string[]
     */
    public function getSearchableAttributeCodes() : array
    {
        return [];
    }

    public function createProductListingFacetFiltersToIncludeInResult() : FacetFiltersToIncludeInResult
    {
        return new FacetFiltersToIncludeInResult();
    }

    public function createKeyValueStore() : KeyValueStore
    {
        return $this->createMock(KeyValueStore::class);
    }

    public function createEventQueue() : DomainEventQueue
    {
        return $this->createMock(DomainEventQueue::class);
    }

    public function createEventMessageQueue() : Queue
    {
        return $this->createMock(Queue::class);
    }

    public function createCommandQueue() : CommandQueue
    {
        return $this->createMock(CommandQueue::class);
    }

    public function createCommandMessageQueue() : Queue
    {
        return $this->createMock(Queue::class);
    }

    public function createLogger() : Logger
    {
        return $this->createMock(Logger::class);
    }

    public function createSearchEngine() : SearchEngine
    {
        return $this->createMock(SearchEngine::class);
    }

    public function createUrlKeyStore() : UrlKeyStore
    {
        return $this->createMock(UrlKeyStore::class);
    }

    public function createImageProcessorCollection() : ImageProcessorCollection
    {
        return $this->createMock(ImageProcessorCollection::class);
    }

    public function createImageProcessor() : ImageProcessor
    {
        return $this->createMock(ImageProcessor::class);
    }

    public function createFileStorageReader() : FileStorageReader
    {
        return $this->createMock(FileStorageReader::class);
    }

    public function createFileStorageWriter() : FileStorageWriter
    {
        return $this->createMock(FileStorageWriter::class);
    }

    public function createBaseUrlBuilder() : BaseUrlBuilder
    {
        return $this->createMock(BaseUrlBuilder::class);
    }

    public function createImageProcessingStrategySequence() : ImageProcessingStrategy
    {
        return $this->createMock(ImageProcessingStrategy::class);
    }

    public function getKeyValueStore() : KeyValueStore
    {
        if (null === $this->keyValueStore) {
            $this->keyValueStore = $this->createKeyValueStore();
        }

        return $this->keyValueStore;
    }

    public function getEventQueue() : DomainEventQueue
    {
        if (null === $this->eventQueue) {
            $this->eventQueue = $this->createEventQueue();
        }

        return $this->eventQueue;
    }

    public function getCommandQueue() : CommandQueue
    {
        if (null === $this->commandQueue) {
            $this->commandQueue = $this->createCommandQueue();
        }

        return $this->commandQueue;
    }

    public function getSearchEngine() : SearchEngine
    {
        if (null === $this->searchEngine) {
            $this->searchEngine = $this->createSearchEngine();
        }

        return $this->searchEngine;
    }

    public function getUrlKeyStore() : UrlKeyStore
    {
        if (null === $this->urlKeyStore) {
            $this->urlKeyStore = $this->createUrlKeyStore();
        }

        return $this->urlKeyStore;
    }

    /**
     * @return SortBy[]
     */
    public function getProductListingSortBy() : array
    {
        return [$this->createMock(SortBy::class)];
    }

    /**
     * @return SortBy[]
     */
    public function getProductSearchSortBy() : array
    {
        return [$this->createMock(SortBy::class)];
    }

    public function getFileStorageBasePathConfig() : string
    {
        return '';
    }

    public function createFacetFieldTransformationRegistry() : FacetFieldTransformationRegistry
    {
        return $this->createMock(FacetFieldTransformationRegistry::class);
    }

    public function createTaxableCountries() : TaxableCountries
    {
        return $this->createMock(TaxableCountries::class);
    }

    public function createProductViewLocator() : ProductViewLocator
    {
        return $this->createMock(ProductViewLocator::class);
    }

    public function createTaxServiceLocator() : TaxServiceLocator
    {
        return $this->createMock(TaxServiceLocator::class);
    }

    public function createGlobalProductListingCriteria() : SearchCriteria
    {
        return $this->createMock(SearchCriteria::class);
    }

    public function createProductImageFileLocator() : ProductImageFileLocator
    {
        return $this->createMock(ProductImageFileLocator::class);
    }

    public function getProductsPerPageConfig() : ProductsPerPage
    {
        return $this->createMock(ProductsPerPage::class);
    }

    /**
     * @return string[]
     */
    public function getSortableAttributeCodes() : array
    {
        return [];
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
        return $this->getCommonFacetFilterRequestFields();
    }

    /**
     * @return string[]
     */
    public function getFacetFilterRequestFieldCodesForSearchDocuments() : array
    {
        return [];
    }

    /**
     * @return FacetFilterRequestField[]
     */
    private function getCommonFacetFilterRequestFields() : array
    {
        return [];
    }

    public function createSearchFieldToRequestParamMap() : SearchFieldToRequestParamMap
    {
        return $this->createMock(SearchFieldToRequestParamMap::class);
    }

    public function createThemeLocator() : ThemeLocator
    {
        return $this->createMock(ThemeLocator::class);
    }

    public function createContextSource() : ContextSource
    {
        return $this->createMock(ContextSource::class);
    }

    public function createLocaleContextPartBuilder() : ContextPartBuilder
    {
        return $this->createMock(ContextPartBuilder::class);
    }

    public function createCountryContextPartBuilder() : ContextPartBuilder
    {
        return $this->createMock(ContextPartBuilder::class);
    }

    public function createWebsiteContextPartBuilder() : ContextPartBuilder
    {
        return $this->createMock(ContextPartBuilder::class);
    }

    public function createApiRouter() : ApiRouter
    {
        return $this->createMock(ApiRouter::class);
    }

    public function getMaxAllowedProductsPerSearchResultsPage() : int
    {
        return 120;
    }

    public function getDefaultNumberOfProductsPerSearchResultsPage() : int
    {
        return 20;
    }

    public function getDefaultSearchResultsPageSortBy() : SortBy
    {
        return $this->createMock(SortBy::class);
    }
}
