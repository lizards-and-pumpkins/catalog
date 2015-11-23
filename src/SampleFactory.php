<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentDelivery\Catalog\FilterNavigationPriceRangesBuilder;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection;
use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\EuroPriceRangeTransformation;
use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\KeyValue\File\FileKeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequest;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestRangedField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField;
use LizardsAndPumpkins\DataPool\SearchEngine\FileSearchEngine;
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
use LizardsAndPumpkins\Queue\File\FileQueue;
use LizardsAndPumpkins\Queue\Queue;

class SampleFactory implements Factory
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
     * @return string[]
     */
    public function getSearchableAttributeCodes()
    {
        return ['name', 'category', 'brand'];
    }

    /**
     * @return string[]
     */
    public function getProductListingFilterNavigationConfig()
    {
        return new FacetFilterRequest(
            new FacetFilterRequestSimpleField(AttributeCode::fromString('gender')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('brand')),
            new FacetFilterRequestRangedField(
                AttributeCode::fromString('price'),
                ...FilterNavigationPriceRangesBuilder::getPriceRanges()
            ),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('color'))
        );
    }

    /**
     * @return string[]
     */
    public function getProductSearchResultsFilterNavigationConfig()
    {
        return new FacetFilterRequest(
            new FacetFilterRequestSimpleField(AttributeCode::fromString('gender')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('brand')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('category')),
            new FacetFilterRequestRangedField(
                AttributeCode::fromString('price'),
                ...FilterNavigationPriceRangesBuilder::getPriceRanges()
            ),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('color'))
        );
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
     * @return FileSearchEngine
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
        $registry = new FacetFieldTransformationRegistry;
        $registry->register('price', new EuroPriceRangeTransformation);

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
        
        $resultImageDir = __DIR__ . '/../pub/media/product/original';
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

        $resultImageDir = __DIR__ . '/../pub/media/product/large';
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

        $resultImageDir = __DIR__ . '/../pub/media/product/medium';
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

        $resultImageDir = __DIR__ . '/../pub/media/product/small';
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
        
        $resultImageDir = __DIR__ . '/../pub/media/product/search-autosuggestion';
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
}
