<?php

namespace Brera;

use Brera\DataPool\KeyValue\File\FileKeyValueStore;
use Brera\DataPool\SearchEngine\FileSearchEngine;
use Brera\Image\ImageMagickInscribeCommand;
use Brera\Image\ImageProcessor;
use Brera\Image\ImageProcessorCollection;
use Brera\Image\ImageProcessorCommandSequence;
use Brera\Queue\InMemory\InMemoryQueue;

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
     * @return InMemoryQueue
     */
    public function createEventQueue()
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
     * @return FileSearchEngine
     */
    public function createSearchEngine()
    {
        $searchEngineStoragePath = sys_get_temp_dir() . '/brera/search-engine';
        $this->createDirectoryIfNotExists($searchEngineStoragePath);

        return FileSearchEngine::withPath($searchEngineStoragePath);
    }

    /**
     * @return string[]
     */
    public function getSearchableAttributeCodes()
    {
        return ['name', 'category'];
    }

    /**
     * @return ImageProcessorCollection
     */
    public function createImageProcessorCollection()
    {
        $processorCollection = new ImageProcessorCollection();
        $processorCollection->add($this->getMasterFactory()->getOriginalImageProcessor());
        $processorCollection->add($this->getMasterFactory()->getProductDetailsPageImageProcessor());
        $processorCollection->add($this->getMasterFactory()->getProductListingImageProcessor());
        $processorCollection->add($this->getMasterFactory()->getGalleyThumbnailImageProcessor());

        return $processorCollection;
    }

    /**
     * @return ImageProcessor
     */
    public function getOriginalImageProcessor()
    {
        $commandSequence = $this->getMasterFactory()->getOriginalImageProcessorCommandSequence();
        $fileStorageReader = $this->getMasterFactory()->getOriginalImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->getOriginalImageFileStorageWriter();

        return new ImageProcessor($commandSequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function getOriginalImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture');
    }

    /**
     * @return FileStorageWriter
     */
    public function getOriginalImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/original';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessorCommandSequence
     */
    public function getOriginalImageProcessorCommandSequence()
    {
        return new ImageProcessorCommandSequence();
    }

    /**
     * @return ImageProcessor
     */
    public function getProductDetailsPageImageProcessor()
    {
        $commandSequence = $this->getMasterFactory()->getProductDetailsPageImageProcessorCommandSequence();
        $fileStorageReader = $this->getMasterFactory()->getProductDetailsPageImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->getProductDetailsPageImageFileStorageWriter();

        return new ImageProcessor($commandSequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function getProductDetailsPageImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture');
    }

    /**
     * @return FileStorageWriter
     */
    public function getProductDetailsPageImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/large';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessorCommandSequence
     */
    public function getProductDetailsPageImageProcessorCommandSequence()
    {
        $imageResizeCommand = new ImageMagickInscribeCommand(365, 340, 'white');

        $commandSequence = new ImageProcessorCommandSequence();
        $commandSequence->addCommand($imageResizeCommand);

        return $commandSequence;
    }

    /**
     * @return ImageProcessor
     */
    public function getProductListingImageProcessor()
    {
        $commandSequence = $this->getMasterFactory()->getProductListingImageProcessorCommandSequence();
        $fileStorageReader = $this->getMasterFactory()->getProductListingImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->getProductListingImageFileStorageWriter();

        return new ImageProcessor($commandSequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function getProductListingImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture');
    }

    /**
     * @return FileStorageWriter
     */
    public function getProductListingImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/medium';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessorCommandSequence
     */
    public function getProductListingImageProcessorCommandSequence()
    {
        $imageResizeCommand = new ImageMagickInscribeCommand(188, 115, 'white');

        $commandSequence = new ImageProcessorCommandSequence();
        $commandSequence->addCommand($imageResizeCommand);

        return $commandSequence;
    }

    /**
     * @return ImageProcessor
     */
    public function getGalleyThumbnailImageProcessor()
    {
        $commandSequence = $this->getMasterFactory()->getGalleyThumbnailImageProcessorCommandSequence();
        $fileStorageReader = $this->getMasterFactory()->getGalleyThumbnailImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->getGalleyThumbnailImageFileStorageWriter();

        return new ImageProcessor($commandSequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function getGalleyThumbnailImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture');
    }

    /**
     * @return FileStorageWriter
     */
    public function getGalleyThumbnailImageFileStorageWriter()
    {
        $resultImageDir = __DIR__ . '/../pub/media/product/small';
        $this->createDirectoryIfNotExists($resultImageDir);

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessorCommandSequence
     */
    public function getGalleyThumbnailImageProcessorCommandSequence()
    {
        $imageResizeCommand = new ImageMagickInscribeCommand(48, 48, 'white');

        $commandSequence = new ImageProcessorCommandSequence();
        $commandSequence->addCommand($imageResizeCommand);

        return $commandSequence;
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
