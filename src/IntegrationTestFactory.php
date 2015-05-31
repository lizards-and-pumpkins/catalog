<?php

namespace Brera;

use Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\DataPool\SearchEngine\InMemorySearchEngine;
use Brera\Image\ImageMagickResizeCommand;
use Brera\Image\ImageProcessor;
use Brera\Image\ImageProcessorCollection;
use Brera\Image\ImageProcessorCommandSequence;
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
        return ['name', 'category'];
    }

    /**
     * @return ImageProcessorCollection
     */
    public function createImageProcessorCollection()
    {
        $processorCollection = new ImageProcessorCollection();
        $processorCollection->add($this->getMasterFactory()->getImageProcessor());

        return $processorCollection;
    }

    /**
     * @return ImageProcessor
     */
    public function getImageProcessor()
    {
        $commandSequence = $this->getMasterFactory()->getImageProcessorCommandSequence();
        $fileStorageReader = $this->getMasterFactory()->getImageFileStorageReader();
        $fileStorageWriter = $this->getMasterFactory()->getImageFileStorageWriter();

        return new ImageProcessor($commandSequence, $fileStorageReader, $fileStorageWriter);
    }

    /**
     * @return FileStorageReader
     */
    public function getImageFileStorageReader()
    {
        return new LocalFilesystemStorageReader(__DIR__ . '/../tests/shared-fixture');
    }

    /**
     * @return FileStorageWriter
     */
    public function getImageFileStorageWriter()
    {
        $resultImageDir = sys_get_temp_dir() . '/' . self::PROCESSED_IMAGES_DIR;

        if (!is_dir($resultImageDir)) {
            mkdir($resultImageDir, 0777, true);
        }

        return new LocalFilesystemStorageWriter($resultImageDir);
    }

    /**
     * @return ImageProcessorCommandSequence
     */
    public function getImageProcessorCommandSequence()
    {
        $imageResizeCommand = new ImageMagickResizeCommand(self::PROCESSED_IMAGE_WIDTH, self::PROCESSED_IMAGE_HEIGHT);

        $commandSequence = new ImageProcessorCommandSequence();
        $commandSequence->addCommand($imageResizeCommand);

        return $commandSequence;
    }
}
