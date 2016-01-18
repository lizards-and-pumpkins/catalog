<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentDelivery\Catalog\FilterNavigationPriceRangesBuilder;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductsPerPage;
use LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation\EuroPriceRangeTransformation;
use LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\ContentDelivery\Catalog\Search\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextCountry;
use LizardsAndPumpkins\DataPool\KeyValue\File\FileKeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestRangedField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField;
use LizardsAndPumpkins\DataPool\SearchEngine\FileSearchEngine;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\FileUrlKeyStore;
use LizardsAndPumpkins\Image\ImageMagickInscribeStrategy;
use LizardsAndPumpkins\Image\ImageProcessor;
use LizardsAndPumpkins\Image\ImageProcessorCollection;
use LizardsAndPumpkins\Image\ImageProcessingStrategySequence;
use LizardsAndPumpkins\Log\InMemoryLogger;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Log\Writer\FileLogMessageWriter;
use LizardsAndPumpkins\Log\Writer\LogMessageWriter;
use LizardsAndPumpkins\Log\WritingLoggerDecorator;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Product\ProductImage\TwentyOneRunProductImageFileLocator;
use LizardsAndPumpkins\Product\Tax\TwentyOneRunTaxServiceLocator;
use LizardsAndPumpkins\Projection\Catalog\TwentyOneRunProductViewLocator;
use LizardsAndPumpkins\Queue\File\FileQueue;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Utils\ImageStorage\FilesystemImageStorage;
use LizardsAndPumpkins\Utils\ImageStorage\ImageStorage;
use LizardsAndPumpkins\Website\TwentyOneRunWebsiteToCountryMap;

class TwentyOneRunFactory implements Factory
{
    use FactoryTrait;

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

    /**
     * @return string[]
     */
    public function getSearchableAttributeCodes()
    {
        return ['name', 'brand', 'product_group'];
    }

    /**
     * @param Context $context
     * @return FacetFilterRequestField[]
     */
    public function getProductListingFacetFilterRequestFields(Context $context)
    {
        return array_merge(
            $this->getCommonFacetFilterRequestFields(),
            [$this->createPriceRangeFacetFilterField($context)]
        );
    }

    /**
     * @param Context $context
     * @return FacetFilterRequestField[]
     */
    public function getProductSearchFacetFilterRequestFields(Context $context)
    {
        return array_merge(
            $this->getCommonFacetFilterRequestFields(),
            [$this->createPriceRangeFacetFilterField($context)]
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
            new FacetFilterRequestSimpleField(AttributeCode::fromString('product_group')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('style')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('brand')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('series')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('size')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('color')),
        ];
    }

    /**
     * @param Context $context
     * @return FacetFilterRequestField
     */
    private function createPriceRangeFacetFilterField(Context $context)
    {
        return new FacetFilterRequestRangedField(
            AttributeCode::fromString($this->getPriceFacetFieldNameForContext($context)),
            ...FilterNavigationPriceRangesBuilder::getPriceRanges()
        );
    }

    /**
     * @param Context $context
     * @return string
     */
    public function getPriceFacetFieldNameForContext(Context $context)
    {
        return $this->getPriceFacetFieldNameForCountry($context->getValue(ContextCountry::CODE));
    }

    /**
     * @param string $countryCode
     * @return string
     */
    private function getPriceFacetFieldNameForCountry($countryCode)
    {
        return 'price_incl_tax_' . strtolower($countryCode);
    }

    /**
     * @return string[]
     */
    public function getAdditionalAttributesForSearchIndex()
    {
        return ['backorders', 'stock_qty', 'category', 'created_at'];
    }

    /**
     * @return FileKeyValueStore
     */
    public function createKeyValueStore()
    {
        $baseStorageDir = $this->getMasterFactory()->getFileStorageBasePathConfig();
        $storagePath = $baseStorageDir . '/key-value-store';
        $this->createDirectoryIfNotExists($storagePath);

        return new FileKeyValueStore($storagePath);
    }

    /**
     * @return Queue
     */
    public function createEventQueue()
    {
        $storageBasePath = $this->getMasterFactory()->getFileStorageBasePathConfig();
        $storagePath = $storageBasePath . '/event-queue/content';
        $lockFile = $storageBasePath . '/event-queue/lock';
        return new FileQueue($storagePath, $lockFile);
    }

    /**
     * @return Queue
     */
    public function createCommandQueue()
    {
        $storageBasePath = $this->getMasterFactory()->getFileStorageBasePathConfig();
        $storagePath = $storageBasePath . '/command-queue/content';
        $lockFile = $storageBasePath . '/command-queue/lock';
        return new FileQueue($storagePath, $lockFile);
    }

    /**
     * @return Logger
     */
    public function createLogger()
    {
        return new WritingLoggerDecorator(
            new InMemoryLogger(),
            $this->getMasterFactory()->createLogMessageWriter()
        );
    }

    /**
     * @return LogMessageWriter
     */
    public function createLogMessageWriter()
    {
        return new FileLogMessageWriter($this->getMasterFactory()->getLogFilePathConfig());
    }

    /**
     * @return string
     */
    public function getLogFilePathConfig()
    {
        return __DIR__ . '/../log/system.log';
    }

    /**
     * @return SearchEngine
     */
    public function createSearchEngine()
    {
        $storageBasePath = $this->getMasterFactory()->getFileStorageBasePathConfig();
        $searchEngineStoragePath = $storageBasePath . '/search-engine';
        $this->createDirectoryIfNotExists($searchEngineStoragePath);

        return FileSearchEngine::create(
            $searchEngineStoragePath,
            $this->getMasterFactory()->createSearchCriteriaBuilder(),
            $this->getMasterFactory()->getFacetFieldTransformationRegistry()
        );
    }

    /**
     * @return FileUrlKeyStore
     */
    public function createUrlKeyStore()
    {
        $storageBasePath = $this->getMasterFactory()->getFileStorageBasePathConfig();
        return new FileUrlKeyStore($storageBasePath . '/url-key-store');
    }

    /**
     * @return FacetFieldTransformationRegistry
     */
    public function createFacetFieldTransformationRegistry()
    {
        $registry = new FacetFieldTransformationRegistry();
        $priceTransformation = new EuroPriceRangeTransformation();
        $registry->register('price', $priceTransformation);
        $countries = $this->getMasterFactory()->createTaxableCountries()->getCountries();
        array_map(function ($country) use ($registry, $priceTransformation) {
            $registry->register($this->getPriceFacetFieldNameForCountry($country), $priceTransformation);
        }, $countries);

        return $registry;
    }

    /**
     * @return ImageProcessorCollection
     */
    public function createImageProcessorCollection()
    {
        $processorCollection = new ImageProcessorCollection();
        $processorCollection->add($this->getMasterFactory()->createOriginalImageProcessor());
        $processorCollection->add($this->getMasterFactory()->createProductDetailsPageImageProcessor());
        $processorCollection->add($this->getMasterFactory()->createProductListingImageProcessor());
        $processorCollection->add($this->getMasterFactory()->createGalleyThumbnailImageProcessor());
        $processorCollection->add($this->getMasterFactory()->createSearchAutosuggestionImageProcessor());

        return $processorCollection;
    }

    /**
     * @return ImageProcessor
     */
    public function createOriginalImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->createOriginalImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createFileStorageWriter();

        $resultImageDir = $this->getMasterFactory()->getMediaBaseDirectoryConfig() . '/product/' .
            TwentyOneRunProductImageFileLocator::ORIGINAL;

        $this->createDirectoryIfNotExists($resultImageDir);

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
     * @return ImageProcessingStrategySequence
     */
    public function createOriginalImageProcessingStrategySequence()
    {
        return new ImageProcessingStrategySequence();
    }

    /**
     * @return ImageProcessor
     */
    public function createProductDetailsPageImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->createProductDetailsPageImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createFileStorageWriter();

        $resultImageDir = $this->getMasterFactory()->getMediaBaseDirectoryConfig() . '/product/' .
            TwentyOneRunProductImageFileLocator::LARGE;

        $this->createDirectoryIfNotExists($resultImageDir);

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter, $resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function createProductDetailsPageImageProcessingStrategySequence()
    {
        $imageResizeStrategy = new ImageMagickInscribeStrategy(365, 340, 'white');

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @return ImageProcessor
     */
    public function createProductListingImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->createProductListingImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createFileStorageWriter();

        $resultImageDir = $this->getMasterFactory()->getMediaBaseDirectoryConfig() . '/product/' .
            TwentyOneRunProductImageFileLocator::MEDIUM;

        $this->createDirectoryIfNotExists($resultImageDir);

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter, $resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function createProductListingImageProcessingStrategySequence()
    {
        $imageResizeStrategy = new ImageMagickInscribeStrategy(188, 115, 'white');

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @return ImageProcessor
     */
    public function createGalleyThumbnailImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->createGalleyThumbnailImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createFileStorageWriter();

        $resultImageDir = $this->getMasterFactory()->getMediaBaseDirectoryConfig() . '/product/' .
            TwentyOneRunProductImageFileLocator::SMALL;

        $this->createDirectoryIfNotExists($resultImageDir);

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter, $resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function createGalleyThumbnailImageProcessingStrategySequence()
    {
        $imageResizeStrategy = new ImageMagickInscribeStrategy(48, 48, 'white');

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @return ImageProcessor
     */
    public function createSearchAutosuggestionImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->createSearchAutosuggestionImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createFileStorageWriter();

        $resultImageDir = $this->getMasterFactory()->getMediaBaseDirectoryConfig() . '/product/' .
            TwentyOneRunProductImageFileLocator::SEARCH_AUTOSUGGESTION;

        $this->createDirectoryIfNotExists($resultImageDir);

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter, $resultImageDir);
    }

    /**
     * @return ImageProcessingStrategySequence
     */
    public function createSearchAutosuggestionImageProcessingStrategySequence()
    {
        $imageResizeStrategy = new ImageMagickInscribeStrategy(60, 37, 'white');

        $strategySequence = new ImageProcessingStrategySequence();
        $strategySequence->add($imageResizeStrategy);

        return $strategySequence;
    }

    /**
     * @return string
     */
    public function getFileStorageBasePathConfig()
    {
        /** @var ConfigReader $configReader */
        $configReader = $this->getMasterFactory()->createConfigReader();
        $basePath = $configReader->get('file_storage_base_path');
        return null === $basePath ?
            sys_get_temp_dir() . '/lizards-and-pumpkins' :
            $basePath;
    }

    /**
     * @param string $path
     */
    private function createDirectoryIfNotExists($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
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
                SortOrderConfig::create(
                    AttributeCode::fromString('price'),
                    SortOrderDirection::create(SortOrderDirection::ASC)
                ),
                SortOrderConfig::create(
                    AttributeCode::fromString('created_at'),
                    SortOrderDirection::create(SortOrderDirection::DESC)
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
                SortOrderConfig::create(
                    AttributeCode::fromString('price'),
                    SortOrderDirection::create(SortOrderDirection::ASC)
                ),
                SortOrderConfig::create(
                    AttributeCode::fromString('created_at'),
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
     * @return ProductsPerPage
     */
    public function getProductsPerPageConfig()
    {
        if (null === $this->memoizedProductsPerPageConfig) {
            $numbersOfProductsPerPage = [60, 120];
            $selectedNumberOfProductsPerPage = 60;

            $this->memoizedProductsPerPageConfig = ProductsPerPage::create(
                $numbersOfProductsPerPage,
                $selectedNumberOfProductsPerPage
            );
        }

        return $this->memoizedProductsPerPageConfig;
    }

    /**
     * @return TwentyOneRunWebsiteToCountryMap
     */
    public function createWebsiteToCountryMap()
    {
        return new TwentyOneRunWebsiteToCountryMap();
    }

    /**
     * @return TaxableCountries
     */
    public function createTaxableCountries()
    {
        return new TwentyOneRunTaxableCountries();
    }

    /**
     * @return TwentyOneRunTaxServiceLocator
     */
    public function createTaxServiceLocator()
    {
        return new TwentyOneRunTaxServiceLocator();
    }

    /**
     * @return TwentyOneRunProductViewLocator
     */
    public function createProductViewLocator()
    {
        return new TwentyOneRunProductViewLocator(
            $this->getMasterFactory()->createProductImageFileLocator()
        );
    }

    /**
     * @return SearchCriteria
     */
    public function createGlobalProductListingCriteria()
    {
        return CompositeSearchCriterion::createOr(
            SearchCriterionGreaterThan::create('stock_qty', 0),
            SearchCriterionEqual::create('backorders', 'true')
        );
    }

    /**
     * @return ProductImageFileLocator
     */
    public function createProductImageFileLocator()
    {
        return new TwentyOneRunProductImageFileLocator(
            $this->getMasterFactory()->createImageStorage()
        );
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
    public function createSearchFieldToRequestParamMap(Context $context)
    {
        $queryParameter = 'price';
        $facetField = $this->getPriceFacetFieldNameForContext($context);
        $facetFieldToQueryParameterMap = [$facetField => $queryParameter];
        $queryParameterToFacetFieldMap = [$queryParameter => $facetField];
        return new SearchFieldToRequestParamMap(
            $facetFieldToQueryParameterMap,
            $queryParameterToFacetFieldMap
        );
    }
}
