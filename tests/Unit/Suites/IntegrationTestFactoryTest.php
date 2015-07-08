<?php

namespace Brera\Tests\Integration;

use Brera\DataPool\SearchEngine\InMemorySearchEngine;
use Brera\Image\ImageProcessor;
use Brera\Image\ImageProcessorCollection;
use Brera\Image\ImageProcessingStrategySequence;
use Brera\IntegrationTestFactory;
use Brera\InMemoryLogger;
use Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\LocalFilesystemStorageReader;
use Brera\LocalFilesystemStorageWriter;
use Brera\PoCMasterFactory;
use Brera\Queue\InMemory\InMemoryQueue;
use Brera\Utils\LocalFilesystem;

/**
 * @covers \Brera\IntegrationTestFactory
 * @uses   \Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore
 * @uses   \Brera\FactoryTrait
 * @uses   \Brera\Image\ImageMagickResizeStrategy
 * @uses   \Brera\Image\ImageProcessor
 * @uses   \Brera\Image\ImageProcessorCollection
 * @uses   \Brera\Image\ImageProcessingStrategySequence
 * @uses   \Brera\InMemoryLogger
 * @uses   \Brera\LocalFilesystemStorageReader
 * @uses   \Brera\LocalFilesystemStorageWriter
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\Queue\InMemory\InMemoryQueue
 * @uses   \Brera\Utils\LocalFilesystem
 */
class IntegrationTestFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationTestFactory
     */
    private $factory;

    public function setUp()
    {
        $masterFactory = new PoCMasterFactory();
        $this->factory = new IntegrationTestFactory();
        $masterFactory->register($this->factory);
    }

    public function testInMemoryKeyValueStoreIsReturned()
    {
        $this->assertInstanceOf(InMemoryKeyValueStore::class, $this->factory->createKeyValueStore());
    }

    public function testInMemoryEventQueueIsReturned()
    {
        $this->assertInstanceOf(InMemoryQueue::class, $this->factory->createEventQueue());
    }

    public function testInMemoryCommandQueueIsReturned()
    {
        $this->assertInstanceOf(InMemoryQueue::class, $this->factory->createCommandQueue());
    }

    public function testInMemoryLoggerIsReturned()
    {
        $this->assertInstanceOf(InMemoryLogger::class, $this->factory->createLogger());
    }

    public function testInMemorySearchEngineIsReturned()
    {
        $this->assertInstanceOf(InMemorySearchEngine::class, $this->factory->createSearchEngine());
    }

    public function testLocalFilesystemStorageWriterIsReturned()
    {
        $this->assertInstanceOf(LocalFilesystemStorageWriter::class, $this->factory->getImageFileStorageWriter());
    }

    public function testLocalFilesystemStorageReaderIsReturned()
    {
        $this->assertInstanceOf(LocalFilesystemStorageReader::class, $this->factory->getImageFileStorageReader());
    }

    public function testResizedImagesDirectoryIsCreated()
    {
        $resultImageDir = sys_get_temp_dir() . '/' . IntegrationTestFactory::PROCESSED_IMAGES_DIR;

        (new LocalFilesystem())->removeDirectoryAndItsContent($resultImageDir);

        $this->factory->getImageFileStorageWriter();

        $this->assertTrue(is_dir($resultImageDir));
    }

    public function testImageProcessingStrategySequenceIsReturned()
    {
        $this->assertInstanceOf(
            ImageProcessingStrategySequence::class,
            $this->factory->getImageProcessingStrategySequence()
        );
    }

    public function testArrayOfSearchableAttributeCodesIsReturned()
    {
        $this->assertInternalType('array', $this->factory->getSearchableAttributeCodes());
    }

    public function testImageProcessorCollectionIsReturned()
    {
        $this->assertInstanceOf(ImageProcessorCollection::class, $this->factory->createImageProcessorCollection());
    }

    public function testImageProcessorIsReturned()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->getImageProcessor());
    }
}
