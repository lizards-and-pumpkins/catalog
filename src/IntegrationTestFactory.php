<?php

namespace Brera;

use Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\DataPool\SearchEngine\InMemorySearchEngine;
use Brera\DataPool\SearchEngine\SearchEngine;
use Brera\Image\ImageMagickResizeStrategy;
use Brera\Image\ImageProcessor;
use Brera\Image\ImageProcessorCollection;
use Brera\Image\ImageProcessingStrategySequence;
use Brera\Queue\InMemory\InMemoryQueue;
use Brera\Queue\Queue;

class IntegrationTestFactory implements Factory
{
    use FactoryTrait;

    const PROCESSED_IMAGES_DIR = 'brera/processed-images';
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
     * @return string[]
     */
    public function getSearchableAttributeCodesConfig()
    {
        return ['name', 'category', 'brand'];
    }

    /**
     * @return string[]
     */
    public function getProductListingFilterNavigationAttributeCodesConfig()
    {
        return ['brand', 'gender'];
    }

    /**
     * @return string[]
     */
    public function getProductSearchResultsFilterNavigationAttributeCodesConfig()
    {
        return ['brand', 'category', 'gender'];
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
        return new InMemorySearchEngine();
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
}
