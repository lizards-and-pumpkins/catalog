<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\BaseUrl\IntegrationTestFixedBaseUrlBuilder;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection;
use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequest;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField;
use LizardsAndPumpkins\DataPool\SearchEngine\InMemorySearchEngine;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\InMemoryUrlKeyStore;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Image\ImageMagickResizeStrategy;
use LizardsAndPumpkins\Image\ImageProcessor;
use LizardsAndPumpkins\Image\ImageProcessorCollection;
use LizardsAndPumpkins\Image\ImageProcessingStrategySequence;
use LizardsAndPumpkins\Log\InMemoryLogger;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Projection\Catalog\IntegrationTestProductViewLocator;
use LizardsAndPumpkins\Projection\Catalog\ProductViewLocator;
use LizardsAndPumpkins\Queue\InMemory\InMemoryQueue;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Tax\IntegrationTestTaxServiceLocator;
use LizardsAndPumpkins\Utils\ImageStorage\FilesystemImageStorage;
use LizardsAndPumpkins\Utils\ImageStorage\ImageStorage;
use LizardsAndPumpkins\Website\HostToWebsiteMap;
use LizardsAndPumpkins\Website\WebsiteToCountryMap;

class IntegrationTestFactory implements Factory
{
    use FactoryTrait;

    const PROCESSED_IMAGES_DIR = 'lizards-and-pumpkins/processed-images';
    const PROCESSED_IMAGE_WIDTH = 40;
    const PROCESSED_IMAGE_HEIGHT = 20;

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
     * @var SortOrderConfig[]
     */
    private $memoizedProductListingSortOrderConfig;

    /**
     * @var SortOrderConfig[]
     */
    private $memoizedProductSearchSortOrderConfig;

    /**
     * @var SortOrderConfig
     */
    private $memoizedProductSearchAutosuggestionSortOrderConfig;

    public function __construct(MasterFactory $masterFactory)
    {
        $masterFactory->register($this);
    }

    /**
     * @return string[]
     */
    public function getSearchableAttributeCodes()
    {
        return ['name', 'category', 'brand'];
    }

    /**
     * @return array[]
     */
    public function getProductListingFilterNavigationConfig()
    {
        return new FacetFilterRequest(
            new FacetFilterRequestSimpleField(AttributeCode::fromString('gender')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('brand')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('price')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('color'))
        );
    }

    /**
     * @return array[]
     */
    public function getProductSearchResultsFilterNavigationConfig()
    {
        return new FacetFilterRequest(
            new FacetFilterRequestSimpleField(AttributeCode::fromString('gender')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('brand')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('category')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('price')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('color'))
        );
    }

    /**
     * @return InMemoryKeyValueStore
     */
    public function createKeyValueStore()
    {
        return new InMemoryKeyValueStore();
    }

    /**
     * @return InMemoryQueue
     */
    public function createEventQueue()
    {
        return new InMemoryQueue();
    }

    /**
     * @return InMemoryQueue
     */
    public function createCommandQueue()
    {
        return new InMemoryQueue();
    }

    /**
     * @return InMemoryLogger
     */
    public function createLogger()
    {
        return new InMemoryLogger();
    }

    /**
     * @return InMemorySearchEngine
     */
    public function createSearchEngine()
    {
        return new InMemorySearchEngine(
            $this->getMasterFactory()->createSearchCriteriaBuilder(),
            $this->getMasterFactory()->getFacetFieldTransformationRegistry()
        );
    }

    /**
     * @return FacetFieldTransformationRegistry
     */
    public function createFacetFieldTransformationRegistry()
    {
        return new FacetFieldTransformationRegistry;
    }

    /**
     * @return UrlKeyStore
     */
    public function createUrlKeyStore()
    {
        return new InMemoryUrlKeyStore();
    }

    /**
     * @return ImageProcessorCollection
     */
    public function createImageProcessorCollection()
    {
        $processorCollection = new ImageProcessorCollection();
        $processorCollection->add($this->getMasterFactory()->createImageProcessor());

        return $processorCollection;
    }

    /**
     * @return ImageProcessor
     */
    public function createImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->createImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createFileStorageWriter();
        
        $resultImageDir = $this->getMasterFactory()->getFileStorageBasePathConfig() . '/' . self::PROCESSED_IMAGES_DIR;
        
        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter, $resultImageDir);
    }

    /**
     * @return FileStorageReader
     */
    public function createFileStorageReader()
    {
        return new LocalFilesystemStorageReader();
    }

    /**
     * @return FileStorageWriter
     */
    public function createFileStorageWriter()
    {
        return new LocalFilesystemStorageWriter();
    }

    /**
     * @return IntegrationTestFixedBaseUrlBuilder
     */
    public function createBaseUrlBuilder()
    {
        return new IntegrationTestFixedBaseUrlBuilder();
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function createImageProcessingStrategySequence()
    {
        $imageResizeStrategyClass = $this->locateImageResizeStrategyClass();
        $imageResizeStrategy = new $imageResizeStrategyClass(
            self::PROCESSED_IMAGE_WIDTH,
            self::PROCESSED_IMAGE_HEIGHT
        );

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @return string
     */
    private function locateImageResizeStrategyClass()
    {
        if (extension_loaded('imagick')) {
            return ImageMagickResizeStrategy::class;
        }
        return Image\GdResizeStrategy::class;
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

    public function setKeyValueStore(KeyValueStore $keyValueStore)
    {
        $this->keyValueStore = $keyValueStore;
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

    public function setEventQueue(Queue $eventQueue)
    {
        $this->eventQueue = $eventQueue;
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

    public function setCommandQueue(Queue $commandQueue)
    {
        $this->commandQueue = $commandQueue;
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

    public function setSearchEngine(SearchEngine $searchEngine)
    {
        $this->searchEngine = $searchEngine;
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

    public function setUrlKeyStore(UrlKeyStore $urlKeyStore)
    {
        $this->urlKeyStore = $urlKeyStore;
    }

    /**
     * @return string
     */
    public function getFileStorageBasePathConfig()
    {
        return sys_get_temp_dir();
    }

    /**
     * @return SortOrderConfig[]
     */
    public function getProductListingSortOrderConfig()
    {
        if (null === $this->memoizedProductListingSortOrderConfig) {
            $this->memoizedProductListingSortOrderConfig = [
                SortOrderConfig::createSelected(
                    AttributeCode::fromString('name'),
                    SortOrderDirection::create(SortOrderDirection::ASC)
                ),
            ];
        }

        return $this->memoizedProductListingSortOrderConfig;
    }

    /**
     * @return SortOrderConfig[]
     */
    public function getProductSearchSortOrderConfig()
    {
        if (null === $this->memoizedProductSearchSortOrderConfig) {
            $this->memoizedProductSearchSortOrderConfig = [
                SortOrderConfig::createSelected(
                    AttributeCode::fromString('name'),
                    SortOrderDirection::create(SortOrderDirection::ASC)
                ),
            ];
        }

        return $this->memoizedProductSearchSortOrderConfig;
    }

    /**
     * @return SortOrderConfig
     */
    public function getProductSearchAutosuggestionSortOrderConfig()
    {
        if (null === $this->memoizedProductSearchAutosuggestionSortOrderConfig) {
            $this->memoizedProductSearchAutosuggestionSortOrderConfig = SortOrderConfig::createSelected(
                AttributeCode::fromString('name'),
                SortOrderDirection::create(SortOrderDirection::ASC)
            );
        }

        return $this->memoizedProductSearchAutosuggestionSortOrderConfig;
    }

    /**
     * @return HostToWebsiteMap
     */
    public function createHostToWebsiteMap()
    {
        return new IntegrationTestHostToWebsiteMap();
    }

    /**
     * @return WebsiteToCountryMap
     */
    public function createWebsiteToCountryMap()
    {
        return new IntegrationTestWebsiteToCountryMap();
    }

    /**
     * @return TaxableCountries
     */
    public function createTaxableCountries()
    {
        return new IntegrationTestTaxableCountries();
    }

    /**
     * @return IntegrationTestTaxServiceLocator
     */
    public function createTaxServiceLocator()
    {
        return new IntegrationTestTaxServiceLocator();
    }

    /**
     * @return ProductViewLocator
     */
    public function createProductViewLocator()
    {
        return new IntegrationTestProductViewLocator();
    }

    /**
     * @return SearchCriteria
     */
    public function createGlobalProductListingCriteria()
    {
        return SearchCriterionGreaterThan::create('stock_qty', 0);
    }

    /**
     * @return ProductImageFileLocator
     */
    public function createProductImageFileLocator()
    {
        return new IntegrationTestProductImageFileLocator($this->getMasterFactory()->createImageStorage());
    }

    /**
     * @return ImageStorage
     */
    public function createImageStorage()
    {
        return new FilesystemImageStorage(
            $this->getMasterFactory()->createFilesystemFileStorage(),
            $this->getMasterFactory()->createMediaBaseUrlBuilder(),
            $this->getMasterFactory()->getMediaBaseDirectoryConfig()
        );
    }
}
