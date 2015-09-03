<?php

namespace Brera;

use Brera\DataPool\KeyValue\File\FileKeyValueStore;
use Brera\DataPool\SearchEngine\FileSearchEngine;
use Brera\Image\ImageMagickInscribeStrategy;
use Brera\Image\ImageProcessor;
use Brera\Image\ImageProcessorCollection;
use Brera\Image\ImageProcessingStrategySequence;
use Brera\Log\InMemoryLogger;
use Brera\Log\Writer\FileLogMessageWriter;
use Brera\Log\Writer\LogMessageWriter;
use Brera\Log\PersistingLoggerDecorator;
use Brera\Queue\File\FileQueue;
use Brera\Queue\Queue;

class SampleFactory implements Factory
{
    use FactoryTrait;

    /**
     * @return FileKeyValueStore
     */
    public function createKeyValueStore()
    {
        $storagePath = sys_get_temp_dir() . '/brera/key-value-store';
        $this->createDirectoryIfNotExists($storagePath);

        return new FileKeyValueStore($storagePath);
    }

    /**
     * @return Queue
     */
    public function createEventQueue()
    {
        $storagePath = sys_get_temp_dir() . '/brera/event-queue/content';
        $lockFile = sys_get_temp_dir() . '/brera/event-queue/lock';
        return new FileQueue($storagePath, $lockFile);
    }

    /**
     * @return Queue
     */
    public function createCommandQueue()
    {
        $storagePath = sys_get_temp_dir() . '/brera/command-queue/content';
        $lockFile = sys_get_temp_dir() . '/brera/command-queue/lock';
        return new FileQueue($storagePath, $lockFile);
    }

    /**
     * @return Logger
     */
    public function createLogger()
    {
        return new PersistingLoggerDecorator(
            new InMemoryLogger(),
            $this->getMasterFactory()->createLogMessagePersister()
        );
    }

    /**
     * @return LogMessageWriter
     */
    public function createLogMessagePersister()
    {
        return new FileLogMessagePersister($this->getMasterFactory()->getLogFilePathConfig());
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
        $searchEngineStoragePath = sys_get_temp_dir() . '/brera/search-engine';
        $this->createDirectoryIfNotExists($searchEngineStoragePath);

        return FileSearchEngine::create($searchEngineStoragePath);
    }

    /**
     * @return string[]
     */
    public function getSearchableAttributeCodes()
    {
        return ['name', 'category', 'brand'];
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
        $fileStorageReader = $this->getMasterFactory()->createOriginalImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createOriginalImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function createOriginalImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function createOriginalImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/original';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
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
        $fileStorageReader = $this->getMasterFactory()->createProductDetailsPageImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createProductDetailsPageImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function createProductDetailsPageImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function createProductDetailsPageImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/large';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
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
        $fileStorageReader = $this->getMasterFactory()->createProductListingImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createProductListingImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function createProductListingImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function createProductListingImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/medium';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
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
        $fileStorageReader = $this->getMasterFactory()->createGalleyThumbnailImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createGalleyThumbnailImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function createGalleyThumbnailImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function createGalleyThumbnailImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/small';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
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
        $fileStorageReader = $this->getMasterFactory()->createSearchAutosuggestionImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createSearchAutosuggestionImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function createSearchAutosuggestionImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function createSearchAutosuggestionImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/search-autosuggestion';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
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
     * @param string $path
     */
    private function createDirectoryIfNotExists($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
}
