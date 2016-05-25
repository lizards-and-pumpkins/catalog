<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Import\FileStorage\FileStorageReader;
use LizardsAndPumpkins\Import\FileStorage\FileStorageWriter;
use LizardsAndPumpkins\Import\Tax\TaxableCountries;
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
    public function createProductListingFacetFiltersToIncludeInResult()
    {
        return new FacetFiltersToIncludeInResult();
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
     * @return TaxableCountries
     */
    public function createTaxableCountries()
    {
        return $this->mockObjectGenerator->getMock(TaxableCountries::class);
    }

    /**
     * @return ProductViewLocator
     */
    public function createProductViewLocator()
    {
        return $this->mockObjectGenerator->getMock(ProductViewLocator::class);
    }

    /**
     * @return TaxServiceLocator
     */
    public function createTaxServiceLocator()
    {
        return $this->mockObjectGenerator->getMock(TaxServiceLocator::class);
    }

    /**
     * @return SearchCriteria
     */
    public function createGlobalProductListingCriteria()
    {
        return $this->mockObjectGenerator->getMock(SearchCriteria::class);
    }

    /**
     * @return ProductImageFileLocator
     */
    public function createProductImageFileLocator()
    {
        return $this->mockObjectGenerator->getMock(ProductImageFileLocator::class);
    }

    /**
     * @return ProductsPerPage
     */
    public function getProductsPerPageConfig()
    {
        return $this->mockObjectGenerator->getMock(ProductsPerPage::class, [], [], '', false);
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
        return $this->mockObjectGenerator->getMock(SearchFieldToRequestParamMap::class, [], [], '', false);
    }

    /**
     * @return ThemeLocator
     */
    public function createThemeLocator()
    {
        return $this->mockObjectGenerator->getMock(ThemeLocator::class, [], [], '', false);
    }

    /**
     * @return ContextSource
     */
    public function createContextSource()
    {
        return $this->mockObjectGenerator->getMock(ContextSource::class, [], [], '', false);
    }

    /**
     * @return ContextPartBuilder
     */
    public function createLocaleContextPartBuilder()
    {
        return $this->mockObjectGenerator->getMock(ContextPartBuilder::class);
    }

    /**
     * @return ContextPartBuilder
     */
    public function createCountryContextPartBuilder()
    {
        return $this->mockObjectGenerator->getMock(ContextPartBuilder::class);
    }

    /**
     * @return ContextPartBuilder
     */
    public function createWebsiteContextPartBuilder()
    {
        return $this->mockObjectGenerator->getMock(ContextPartBuilder::class);
    }
}
