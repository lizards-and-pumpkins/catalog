<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Import\FileStorage\FileStorageReader;
use LizardsAndPumpkins\Import\FileStorage\FileStorageWriter;
use LizardsAndPumpkins\Import\Tax\TaxableCountries;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
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
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\Context\Website\WebsiteToCountryMap;
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
    public function getSearchableAttributeCodes(): array
    {
        return [];
    }

    public function createProductListingFacetFiltersToIncludeInResult(): FacetFiltersToIncludeInResult
    {
        return new FacetFiltersToIncludeInResult();
    }

    public function createKeyValueStore(): KeyValueStore
    {
        return $this->mockObjectGenerator->getMock(KeyValueStore::class);
    }

    public function createEventQueue(): DomainEventQueue
    {
        return $this->mockObjectGenerator->getMock(DomainEventQueue::class, [], [], '', false);
    }

    public function createEventMessageQueue(): Queue
    {
        return $this->mockObjectGenerator->getMock(Queue::class);
    }

    public function createCommandQueue(): CommandQueue
    {
        return $this->mockObjectGenerator->getMock(CommandQueue::class, [], [], '', false);
    }

    public function createCommandMessageQueue(): Queue
    {
        return $this->mockObjectGenerator->getMock(Queue::class);
    }

    public function createLogger(): Logger
    {
        return $this->mockObjectGenerator->getMock(Logger::class);
    }

    public function createSearchEngine(): SearchEngine
    {
        return $this->mockObjectGenerator->getMock(SearchEngine::class);
    }

    public function createUrlKeyStore(): UrlKeyStore
    {
        return $this->mockObjectGenerator->getMock(UrlKeyStore::class);
    }

    public function createImageProcessorCollection(): ImageProcessorCollection
    {
        return $this->mockObjectGenerator->getMock(ImageProcessorCollection::class);
    }

    public function createImageProcessor(): ImageProcessor
    {
        return $this->mockObjectGenerator->getMock(ImageProcessor::class, [], [], '', false);
    }

    public function createFileStorageReader(): FileStorageReader
    {
        return $this->mockObjectGenerator->getMock(FileStorageReader::class);
    }

    public function createFileStorageWriter(): FileStorageWriter
    {
        return $this->mockObjectGenerator->getMock(FileStorageWriter::class);
    }

    public function createBaseUrlBuilder(): BaseUrlBuilder
    {
        return $this->mockObjectGenerator->getMock(BaseUrlBuilder::class);
    }

    public function createImageProcessingStrategySequence(): ImageProcessingStrategy
    {
        return $this->mockObjectGenerator->getMock(ImageProcessingStrategy::class);
    }

    public function getKeyValueStore(): KeyValueStore
    {
        if (null === $this->keyValueStore) {
            $this->keyValueStore = $this->createKeyValueStore();
        }
        return $this->keyValueStore;
    }

    public function getEventQueue(): DomainEventQueue
    {
        if (null === $this->eventQueue) {
            $this->eventQueue = $this->createEventQueue();
        }
        return $this->eventQueue;
    }

    public function getCommandQueue(): CommandQueue
    {
        if (null === $this->commandQueue) {
            $this->commandQueue = $this->createCommandQueue();
        }
        return $this->commandQueue;
    }

    public function getSearchEngine(): SearchEngine
    {
        if (null === $this->searchEngine) {
            $this->searchEngine = $this->createSearchEngine();
        }
        return $this->searchEngine;
    }

    public function getUrlKeyStore(): UrlKeyStore
    {
        if (null === $this->urlKeyStore) {
            $this->urlKeyStore = $this->createUrlKeyStore();
        }
        return $this->urlKeyStore;
    }

    /**
     * @return SortOrderConfig[]
     */
    public function getProductListingSortOrderConfig(): array
    {
        return [$this->mockObjectGenerator->getMock(SortOrderConfig::class, [], [], '', false)];
    }

    /**
     * @return SortOrderConfig[]
     */
    public function getProductSearchSortOrderConfig(): array
    {
        return [$this->mockObjectGenerator->getMock(SortOrderConfig::class, [], [], '', false)];
    }

    public function getProductSearchAutosuggestionSortOrderConfig(): SortOrderConfig
    {
        return $this->mockObjectGenerator->getMock(SortOrderConfig::class, [], [], '', false);
    }

    public function getFileStorageBasePathConfig(): string
    {
        return '';
    }

    public function createFacetFieldTransformationRegistry(): FacetFieldTransformationRegistry
    {
        return $this->mockObjectGenerator->getMock(FacetFieldTransformationRegistry::class);
    }

    public function createUrlToWebsiteMap(): UrlToWebsiteMap
    {
        return $this->mockObjectGenerator->getMock(UrlToWebsiteMap::class);
    }

    public function createWebsiteToCountryMap(): WebsiteToCountryMap
    {
        return $this->mockObjectGenerator->getMock(WebsiteToCountryMap::class);
    }

    public function createTaxableCountries(): TaxableCountries
    {
        return $this->mockObjectGenerator->getMock(TaxableCountries::class);
    }

    public function createProductViewLocator(): ProductViewLocator
    {
        return $this->mockObjectGenerator->getMock(ProductViewLocator::class);
    }

    public function createTaxServiceLocator(): TaxServiceLocator
    {
        return $this->mockObjectGenerator->getMock(TaxServiceLocator::class);
    }

    public function createGlobalProductListingCriteria(): SearchCriteria
    {
        return $this->mockObjectGenerator->getMock(SearchCriteria::class);
    }

    public function createProductImageFileLocator(): ProductImageFileLocator
    {
        return $this->mockObjectGenerator->getMock(ProductImageFileLocator::class);
    }

    public function getProductsPerPageConfig(): ProductsPerPage
    {
        return $this->mockObjectGenerator->getMock(ProductsPerPage::class, [], [], '', false);
    }

    /**
     * @return string[]
     */
    public function getAdditionalAttributesForSearchIndex(): array
    {
        return [];
    }

    /**
     * @param Context $context
     * @return FacetFilterRequestField[]
     */
    public function getProductListingFacetFilterRequestFields(Context $context): array
    {
        return $this->getCommonFacetFilterRequestFields();
    }

    /**
     * @param Context $context
     * @return FacetFilterRequestField[]
     */
    public function getProductSearchFacetFilterRequestFields(Context $context): array
    {
        return $this->getCommonFacetFilterRequestFields();
    }

    /**
     * @return string[]
     */
    public function getFacetFilterRequestFieldCodesForSearchDocuments(): array
    {
        return [];
    }

    /**
     * @return FacetFilterRequestField[]
     */
    private function getCommonFacetFilterRequestFields(): array
    {
        return [];
    }

    public function createSearchFieldToRequestParamMap(): SearchFieldToRequestParamMap
    {
        return $this->mockObjectGenerator->getMock(SearchFieldToRequestParamMap::class, [], [], '', false);
    }

    public function createThemeLocator(): ThemeLocator
    {
        return $this->mockObjectGenerator->getMock(ThemeLocator::class, [], [], '', false);
    }

    public function createContextSource(): ContextSource
    {
        return $this->mockObjectGenerator->getMock(ContextSource::class, [], [], '', false);
    }
}
