<?php

namespace Brera;

use Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\DataPool\SearchEngine\InMemorySearchEngine;
use Brera\Image\ImageMagickResizeStrategy;
use Brera\Image\ImageProcessor;
use Brera\Image\ImageProcessorCollection;
use Brera\Image\ImageProcessingStrategySequence;
use Brera\Queue\InMemory\InMemoryQueue;

class IntegrationTestFactory implements Factory
{
    use FactoryTrait;

    const PROCESSED_IMAGES_DIR = 'brera/processed-images';
    const PROCESSED_IMAGE_WIDTH = 40;
    const PROCESSED_IMAGE_HEIGHT = 20;

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
        return new InMemorySearchEngine();
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
        $processorCollection->add($this->getMasterFactory()->createImageProcessor());

        return $processorCollection;
    }

    /**
     * @return ImageProcessor
     */
    public function createImageProcessor()
    {
        $strategySequence = $this->getMasterFactory()->createImageProcessingStrategySequence();
        $fileStorageReader = $this->getMasterFactory()->createImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->createImageFileStorageWriter();

        return new ImageProcessor($strategySequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function createImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture/product-images');
    }

    /**
     * @return FileStorageWriter
     */
    public function createImageFileStorageWriter()
    {
        $resultImageDir = sys_get_temp_dir() . '/' . self::PROCESSED_IMAGES_DIR;

        if (!is_dir($resultImageDir)) {
            mkdir($resultImageDir, 0777, true);
        }

        return new LocalFilesystemStorageWriter($resultImageDir);
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
}
