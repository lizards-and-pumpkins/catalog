<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\BaseUrl\IntegrationTestFixedBaseUrlBuilder;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Context\IntegrationTestContextSource;
use LizardsAndPumpkins\DataPool\KeyValueStore\InMemoryKeyValueStore;
use LizardsAndPumpkins\Import\FileStorage\FileStorageReader;
use LizardsAndPumpkins\Import\FileStorage\FileStorageWriter;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Gd\GdResizeStrategy;
use LizardsAndPumpkins\Import\Tax\TaxableCountries;
use LizardsAndPumpkins\Messaging\Queue\InMemoryQueue;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductsPerPage;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection;
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
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick\ImageMagickResizeStrategy;
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
use LizardsAndPumpkins\Context\Website\HostToWebsiteMap;
use LizardsAndPumpkins\Context\Website\WebsiteToCountryMap;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageReader;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageWriter;

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

    /**
     * @var ProductsPerPage
     */
    private $memoizedProductsPerPageConfig;

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
        return array_merge(
            $this->getCommonFacetFilterRequestFields(),
            [new FacetFilterRequestSimpleField(AttributeCode::fromString('category'))]
        );
    }

    /**
     * @return string[]
     */
    public function getFacetFilterRequestFieldCodesForSearchDocuments()
    {
        return array_map(function (FacetFilterRequestField $field) {
            return (string) $field->getAttributeCode();
        }, $this->getCommonFacetFilterRequestFields());
    }

    /**
     * @return FacetFilterRequestField[]
     */
    private function getCommonFacetFilterRequestFields()
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
    public function getAdditionalAttributesForSearchIndex()
    {
        return ['backorders', 'stock_qty', 'series'];
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
            $this->getMasterFactory()->getSearchableAttributeCodes(),
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
        return GdResizeStrategy::class;
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
     * @return ProductsPerPage
     */
    public function getProductsPerPageConfig()
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
        return new IntegrationTestProductViewLocator($this->getMasterFactory()->createProductImageFileLocator());
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

    /**
     * @return SearchFieldToRequestParamMap
     */
    public function createSearchFieldToRequestParamMap()
    {
        $facetFieldToQueryParameterMap = [];
        $queryParameterToFacetFieldMap = [];
        return new SearchFieldToRequestParamMap($facetFieldToQueryParameterMap, $queryParameterToFacetFieldMap);
    }

    /**
     * @return ThemeLocator
     */
    public function createThemeLocator()
    {
        return ThemeLocator::fromPath(__DIR__ . '/../fixture');
    }

    /**
     * @return ContextSource
     */
    public function createContextSource()
    {
        return new IntegrationTestContextSource($this->getMasterFactory()->createContextBuilder());
    }
}
