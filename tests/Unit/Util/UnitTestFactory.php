<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequest;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Image\ImageProcessingStrategy;
use LizardsAndPumpkins\Image\ImageProcessor;
use LizardsAndPumpkins\Image\ImageProcessorCollection;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Product\Tax\TaxServiceLocator;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Website\HostToWebsiteMap;
use LizardsAndPumpkins\Website\WebsiteToCountryMap;

class UnitTestFactory implements Factory
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
     * @var \PHPUnit_Framework_MockObject_Generator
     */
    private $mockObjectGenerator;

    public function __construct()
    {
        $this->mockObjectGenerator = new \PHPUnit_Framework_MockObject_Generator();
    }

    /**
     * @return string[]
     */
    public function getSearchableAttributeCodes()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getProductListingFilterNavigationConfig()
    {
        return $this->mockObjectGenerator->getMock(FacetFilterRequest::class, [], [], '', true, true, true, true, true);
    }

    /**
     * @return string[]
     */
    public function getProductSearchResultsFilterNavigationConfig()
    {
        return $this->mockObjectGenerator->getMock(FacetFilterRequest::class, [], [], '', true, true, true, true, true);
    }

    /**
     * @return KeyValueStore
     */
    public function createKeyValueStore()
    {
        return $this->mockObjectGenerator->getMock(KeyValueStore::class);
    }

    /**
     * @return Queue
     */
    public function createEventQueue()
    {
        return $this->mockObjectGenerator->getMock(Queue::class);
    }

    /**
     * @return Queue
     */
    public function createCommandQueue()
    {
        return $this->mockObjectGenerator->getMock(Queue::class);
    }

    /**
     * @return Logger
     */
    public function createLogger()
    {
        return $this->mockObjectGenerator->getMock(Logger::class);
    }

    /**
     * @return SearchEngine
     */
    public function createSearchEngine()
    {
        return $this->mockObjectGenerator->getMock(SearchEngine::class);
    }

    /**
     * @return UrlKeyStore
     */
    public function createUrlKeyStore()
    {
        return $this->mockObjectGenerator->getMock(UrlKeyStore::class);
    }

    /**
     * @return ImageProcessorCollection
     */
    public function createImageProcessorCollection()
    {
        return $this->mockObjectGenerator->getMock(ImageProcessorCollection::class);
    }

    /**
     * @return ImageProcessor
     */
    public function createImageProcessor()
    {
        return $this->mockObjectGenerator->getMock(ImageProcessor::class, [], [], '', false);
    }

    /**
     * @return FileStorageReader
     */
    public function createFileStorageReader()
    {
        return $this->mockObjectGenerator->getMock(FileStorageReader::class);
    }

    /**
     * @return FileStorageWriter
     */
    public function createFileStorageWriter()
    {
        return $this->mockObjectGenerator->getMock(FileStorageWriter::class);
    }

    /**
     * @return BaseUrlBuilder
     */
    public function createBaseUrlBuilder()
    {
        return $this->mockObjectGenerator->getMock(BaseUrlBuilder::class);
    }

    /**
     * @return ImageProcessingStrategy
     */
    public function createImageProcessingStrategySequence()
    {
        return $this->mockObjectGenerator->getMock(ImageProcessingStrategy::class);
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
     * @return Queue
     */
    public function getEventQueue()
    {
        if (null === $this->eventQueue) {
            $this->eventQueue = $this->createEventQueue();
        }
        return $this->eventQueue;
    }

    /**
     * @return Queue
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
        return [$this->mockObjectGenerator->getMock(SortOrderConfig::class, [], [], '', false)];
    }

    /**
     * @return SortOrderConfig[]
     */
    public function getProductSearchSortOrderConfig()
    {
        return [$this->mockObjectGenerator->getMock(SortOrderConfig::class, [], [], '', false)];
    }

    /**
     * @return SortOrderConfig
     */
    public function getProductSearchAutosuggestionSortOrderConfig()
    {
        return $this->mockObjectGenerator->getMock(SortOrderConfig::class, [], [], '', false);
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
        return $this->mockObjectGenerator->getMock(FacetFieldTransformationRegistry::class);
    }

    /**
     * @return HostToWebsiteMap
     */
    public function createHostToWebsiteMap()
    {
        return $this->mockObjectGenerator->getMock(HostToWebsiteMap::class);
    }

    /**
     * @return WebsiteToCountryMap
     */
    public function createWebsiteToCountryMap()
    {
        return $this->mockObjectGenerator->getMock(WebsiteToCountryMap::class);
    }

    /**
     * @return TaxableCountries
     */
    public function createTaxableCountries()
    {
        return $this->mockObjectGenerator->getMock(TaxableCountries::class);
    }

    /**
     * @return TaxServiceLocator
     */
    public function createTaxServiceLocator()
    {
        return $this->mockObjectGenerator->getMock(TaxServiceLocator::class);
    }
}
